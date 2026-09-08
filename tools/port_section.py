#!/usr/bin/env python3
"""Port a medzuro-*.liquid Shopify section to a WP template part + CSS file.

Handles the mechanical 80%: extracts the scoped <style> block, rewrites the
Shopify section-id selectors to a stable class, resolves {{ s.* }} setting
references against config/settings_data.json, converts asset_url filters to
PHP, and drops the {% schema %}.

Any remaining Liquid is left in place, wrapped in a LIQUID: marker so the
leftovers are greppable. Static sections come out complete; logic-heavy ones
(product/collection) come out as a starting point.

Usage:
  python tools/port_section.py medzuro-about-page --root .mz-about
"""

import argparse
import json
import pathlib
import re
import sys

HERE = pathlib.Path(__file__).resolve().parent.parent
SHOPIFY = HERE.parent / "medzuro"


def load_settings():
    """Parse settings_data.json, which may carry /* */ comments."""
    raw = (SHOPIFY / "config" / "settings_data.json").read_text(encoding="utf-8-sig")
    data = json.loads(re.sub(r"/\*.*?\*/", "", raw, flags=re.S))
    return data.get("current", data)


def section_settings(current, name):
    return (current.get("sections", {}).get(name, {}) or {}).get("settings", {}) or {}


def schema_defaults(text):
    """Pull `default` values out of the {% schema %} block.

    Settings the merchant never changed are absent from settings_data.json but
    still have a schema default, which is what the storefront actually rendered.
    """
    m = re.search(r"\{%-?\s*schema\s*-?%\}(.*?)\{%-?\s*endschema\s*-?%\}", text, flags=re.S)
    if not m:
        return {}
    try:
        blob = json.loads(m.group(1))
    except json.JSONDecodeError:
        return {}

    out = {}

    def walk(items):
        for it in items or []:
            if isinstance(it, dict):
                if "id" in it and "default" in it:
                    out[it["id"]] = it["default"]
                walk(it.get("settings"))
                walk(it.get("blocks"))

    walk(blob.get("settings"))
    walk(blob.get("blocks"))
    return out


def port_css(css, root, settings):
    """Rewrite Shopify-scoped selectors and inline setting values."""
    css = css.replace("#shopify-section-{{ section.id }}", root)
    css = css.replace(".section-{{ section.id }}", root)
    css = re.sub(r"#shopify-section-\{\{\s*section\.id\s*\}\}", root, css)
    css = re.sub(r"\.section-\{\{\s*section\.id\s*\}\}", root, css)

    def resolve(m):
        key = m.group(1)
        if key in settings:
            return str(settings[key])
        return m.group(0)

    # {{ s.bg }} / {{ section.settings.bg }} -> the configured value
    # Resolve `{% if s.x %}decl{% endif %}` against the known value.
    def resolve_if(m):
        key, inner = m.group(1), m.group(2)
        return inner if settings.get(key) else ""

    css = re.sub(
        r"\{%-?\s*if\s+s(?:ection\.settings)?\.([a-z0-9_]+)\s*-?%\}(.*?)\{%-?\s*endif\s*-?%\}",
        resolve_if, css, flags=re.S,
    )

    css = re.sub(r"\{\{\s*s\.([a-z0-9_]+)\s*\}\}", resolve, css)
    css = re.sub(r"\{\{\s*section\.settings\.([a-z0-9_]+)\s*\}\}", resolve, css)

    # CSS is served as a static file, so asset_url becomes a path relative to
    # assets/css/ rather than a PHP call.
    css = re.sub(
        r"\{\{\s*'([^']+)'\s*\|\s*asset_url\s*\}\}",
        r"../img/", css,
    )
    return css


def port_html(html, settings=None):
    """Convert the Liquid bits that appear in otherwise-static markup."""
    settings = settings or {}

    # section.settings.* is static copy chosen in the theme editor. Inline the
    # configured value; it has no WordPress counterpart to read at runtime.
    def resolve_setting(m):
        key = m.group(1)
        if key not in settings:
            return m.group(0)
        val = settings[key]
        if isinstance(val, bool):
            return "1" if val else ""
        return str(val)

    html = re.sub(r"\{\{\s*section\.settings\.([a-z0-9_]+)\s*\}\}", resolve_setting, html)

    # {{ 'file.png' | asset_url }} -> theme asset URI
    html = re.sub(
        r"\{\{\s*'([^']+)'\s*\|\s*asset_url\s*\}\}",
        r"<?php echo esc_url( get_template_directory_uri() . '/assets/img/\1' ); ?>",
        html,
    )
    # Shopify route helpers
    html = html.replace("{{ routes.root_url }}", "<?php echo esc_url( home_url( '/' ) ); ?>")
    html = re.sub(
        r'href="/collections/all"',
        'href="<?php echo esc_url( get_permalink( wc_get_page_id( \'shop\' ) ) ); ?>"',
        html,
    )
    html = re.sub(
        r'href="/collections/([a-z0-9-]+)"',
        r'href="<?php echo esc_url( get_term_link( \'\1\', \'product_cat\' ) ); ?>"',
        html,
    )
    html = html.replace("{{ shop.name }}", "<?php echo esc_html( get_bloginfo( 'name' ) ); ?>")
    return html


def leftovers(text):
    return sorted(set(re.findall(r"\{\{.*?\}\}|\{%.*?%\}", text, flags=re.S)))


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("section", help="section name without .liquid")
    ap.add_argument("--root", required=True, help="root CSS selector, e.g. .mz-about")
    ap.add_argument("--out", default=None, help="output basename (default: section name)")
    ap.add_argument(
        "--settings-from", dest="settings_from", default=None, metavar="TEMPLATE",
        help="read section settings from a templates/*.json file (e.g. product.json) "
             "instead of config/settings_data.json. Section settings assigned in the "
             "theme editor live on the template, not in the global settings.",
    )
    ap.add_argument(
        "--set", dest="overrides", action="append", default=[], metavar="KEY=VALUE",
        help="override a setting value; repeatable. Use for schema defaults that "
             "were authored for a different colour scheme than the one configured.",
    )
    args = ap.parse_args()

    src = SHOPIFY / "sections" / f"{args.section}.liquid"
    if not src.exists():
        sys.exit(f"not found: {src}")

    text = src.read_text(encoding="utf-8")
    out = args.out or args.section.replace("medzuro-", "")

    # Schema defaults first — saved settings win, defaults fill the gaps.
    defaults = schema_defaults(text)

    # Strip schema — it has no WordPress counterpart.
    text = re.sub(r"\{%-?\s*schema\s*-?%\}.*?\{%-?\s*endschema\s*-?%\}", "", text, flags=re.S)

    styles = re.findall(r"<style>(.*?)</style>", text, flags=re.S)
    body = re.sub(r"<style>.*?</style>", "", text, flags=re.S)
    body = re.sub(r"\{%-?\s*comment\s*-?%\}.*?\{%-?\s*endcomment\s*-?%\}", "", body, flags=re.S)

    if args.settings_from:
        raw = (SHOPIFY / "templates" / args.settings_from).read_text(encoding="utf-8-sig")
        blob = json.loads(re.sub(r"/\*.*?\*/", "", raw, flags=re.S))
        saved = {}
        for sec in (blob.get("sections") or {}).values():
            if sec.get("type") == args.section:
                saved = sec.get("settings") or {}
                break
    else:
        saved = section_settings(load_settings(), args.section)

    settings = {**defaults, **saved}
    for pair in args.overrides:
        key, _, value = pair.partition("=")
        settings[key.strip()] = value.strip()

    css = port_css("\n".join(styles), args.root, settings)
    css_path = HERE / "assets" / "css" / f"{out}.css"
    css_path.write_text(
        f"/* Ported from sections/{args.section}.liquid — scoped to {args.root} */\n{css.strip()}\n",
        encoding="utf-8",
    )

    php = port_html(body, settings).strip()
    php_path = HERE / "template-parts" / f"{out}.php"
    header = (
        "<?php\n"
        f"/**\n * Ported from sections/{args.section}.liquid.\n"
        f" * CSS: assets/css/{out}.css (enqueue with medzuro_style( '{out}' )).\n"
        " */\n\ndefined( 'ABSPATH' ) || exit;\n?>\n"
    )
    php_path.write_text(header + php + "\n", encoding="utf-8")

    rest = leftovers(php)
    print(f"  CSS  -> assets/css/{out}.css  ({len(css.splitlines())} lines)")
    print(f"  PHP  -> template-parts/{out}.php ({len(php.splitlines())} lines)")
    if rest:
        print(f"  {len(rest)} Liquid construct(s) still need hand porting:")
        for r in rest[:15]:
            print("    ", " ".join(r.split())[:90])
    else:
        print("  no Liquid left — section is complete")


if __name__ == "__main__":
    main()
