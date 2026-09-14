# Runbook: connect the repo to WordPress.com

Connects `simer1himanshu/wp-medzuro` to the WordPress.com site so pushes to
`main` deploy the theme automatically.

Written as an operator runbook — follow it top to bottom. Each step says what
to do and how to confirm it worked before moving on. If a check fails, go to
the matching entry in **Troubleshooting** rather than continuing.

WordPress.com changes its dashboard wording regularly. Where a label is quoted,
treat it as "the control that does this", not an exact string. Every step has a
label-independent verification, which is the thing that actually matters.

---

## Facts you need

| Item | Value |
|---|---|
| Repository | `simer1himanshu/wp-medzuro` |
| Branch | `main` |
| Destination directory | `/wp-content/themes/medzuro-wp` |
| Deployment mode | Simple |
| Theme name once activated | Medzuro |

Do **not** set up SFTP credentials or GitHub Actions secrets. The native
integration replaces both.

---

## Preconditions

1. The WordPress.com plan is **Business or Commerce**. Lower tiers cannot
   install custom themes and this runbook will not work on them.
2. You are logged into WordPress.com as the site owner (`medzurowellness`).
3. You are logged into GitHub as `simer1himanshu`, the account that owns the
   repository.

Both logins must be in the same browser. The GitHub authorisation step hands
control back and forth between them, and being signed into the wrong GitHub
account is the most common cause of an empty repository list.

---

## Step 1 — Open the Repositories screen

Go to the site dashboard, then *Settings → Repositories*.

**Verify:** the page title reads "Repositories" and there is a
**Connect repository** button.

---

## Step 2 — Authorise the WordPress.com GitHub app

Click **Connect repository**. If the list is empty and shows
"Check your GitHub connection", click that link instead — it goes to the same
place.

You are redirected to GitHub to install/authorise the WordPress.com app.

When GitHub asks which repositories the app may access, it offers:

- **All repositories**, or
- **Only select repositories**

If you choose "Only select repositories", you **must** tick `wp-medzuro` in
the dropdown before approving.

> This is the step that goes wrong most often. Approving without selecting the
> repository succeeds — GitHub reports the app installed — but WordPress.com
> then shows an empty list, which looks like a different fault entirely.

Approve, and you are returned to WordPress.com.

**Verify:** the repository picker now lists `wp-medzuro`. If it does not, see
Troubleshooting A.

---

## Step 3 — Configure the deployment

Select `wp-medzuro`, then set:

| Field | Value | Notes |
|---|---|---|
| Branch | `main` | The only branch that exists |
| Destination directory | `/wp-content/themes/medzuro-wp` | Must be this exact path |
| Deployment mode | **Simple** | No build step is needed |
| Automatic deployment | **On** | Deploy on every push to `main` |

On destination directory:

- It must end in `medzuro-wp`. WordPress identifies a theme by its folder name,
  and `style.css` must sit directly inside it.
- Do not point it at `/wp-content/themes` alone — that scatters the theme's
  files across the themes directory and no theme will appear.
- Do not point it at the site root.

On deployment mode: choose Simple. Advanced expects a GitHub Actions workflow
to build an artifact first. This repository has a workflow, but it only lints —
selecting Advanced will make deployments wait for an artifact that is never
produced.

Confirm/save.

**Verify:** the Repositories screen now lists `wp-medzuro` with branch `main`
and the destination path shown.

---

## Step 4 — Run the first deployment

The connection may deploy immediately. If not, use the manual trigger
("Deploy now" or similar) on the repository's row.

Wait for it to finish, then open the deployment's log or detail view.

**Verify:** status is success, and the log shows files being written. If it
failed, see Troubleshooting B.

---

## Step 5 — Confirm the files landed

Go to *Appearance → Themes*.

**Verify:** a theme called **Medzuro** appears in the list.

If the deployment succeeded but no theme appears, the destination directory is
wrong — see Troubleshooting C.

---

## Step 6 — Activate the theme

Click **Medzuro → Activate**.

**Verify:** it shows as the active theme, and loading the site front end
returns a page rather than a blank screen or a fatal error.

A blank white screen means a PHP fatal — see Troubleshooting D. **Stop and
resolve it before continuing**; every later step assumes the theme loads.

---

## Step 7 — Confirm automatic deployment works

This proves the pipeline end to end, which is the entire point of the setup.

1. Make a trivial change in the repository — a comment in `style.css` is
   enough.
2. Commit and push to `main`.
3. Watch *Settings → Repositories* for a new deployment.

**Verify:** a deployment starts within about a minute and completes
successfully.

If it does not start, automatic deployment is off — re-open the configuration
from step 3 and enable it.

---

## Step 8 — Continue setup

The theme is now installed and self-updating. Go to **DEPLOY.md §3 onward**
for WooCommerce setup, PHP version, permalinks, pages, menus and products.

Two carried over from there that matter immediately:

- Set PHP to **8.1 or newer** in *Settings → Hosting Configuration*. The theme
  uses arrow functions and will fatal on 7.x.
- Save *Settings → Permalinks* once, even if it looks correct, or product URLs
  will 404.

---

## Troubleshooting

### A. Repository list is empty after authorising

In order of likelihood:

1. **The app was not granted access to this repository.** Go to GitHub →
   *Settings → Applications → Installed GitHub Apps → WordPress.com →
   Configure*, and either select "All repositories" or add `wp-medzuro`.
2. **Wrong GitHub account.** The browser is signed into an account that does
   not own `wp-medzuro`. Sign out, sign in as `simer1himanshu`, retry.
3. **Stale page.** Reload the Repositories screen after changing app access —
   it does not always refresh by itself.

### B. Deployment fails

Open the deployment log and read the error.

- **Permission or path errors** — the destination directory is malformed.
  Re-check it is exactly `/wp-content/themes/medzuro-wp`.
- **Waiting for an artifact / workflow** — deployment mode is set to Advanced.
  Change it to Simple.
- **Repository access revoked** — the GitHub app was removed or its access
  narrowed. Redo step 2.

### C. Deployment succeeded but no theme appears

The files went somewhere other than a valid theme folder. WordPress only
recognises a theme when `style.css` with a `Theme Name:` header sits directly
inside a folder in `wp-content/themes/`.

Check the destination directory ends in `medzuro-wp`. Fix it, redeploy, and if
files were scattered into `wp-content/themes/` directly, remove the strays over
SFTP.

### D. Blank white screen after activating

A PHP fatal. Get the message:

1. Check *Logs → PHP* in the WordPress.com sidebar — the error and its file and
   line number appear there.
2. If nothing useful shows, enable debug logging and reload the site, then read
   `wp-content/debug.log`.

Most likely causes, in order:

- **PHP older than 8.1.** The theme uses arrow functions, which are a parse
  error on 7.x — this fatals at activation with no useful runtime message.
  Raise the PHP version in Hosting Configuration.
- **WooCommerce not active.** The theme calls WooCommerce functions on the
  homepage and product templates. Activate WooCommerce.

Send the error text — file, line and message — and it can be fixed and pushed.

### E. Changes deploy but the site looks unchanged

WordPress.com caches aggressively. Clear the site cache from the hosting
settings, then hard-refresh (Ctrl+F5). Confirm the deployment actually
succeeded before assuming it is a code problem.

---

## What not to do

- Do not add `SFTP_HOST`, `SFTP_USER`, `SFTP_PASSWORD` or `REMOTE_PATH` to the
  GitHub repository. The SFTP workflow is redundant now and stays dormant
  without them. Its lint job still runs on every push, which is worth keeping —
  it catches PHP syntax errors before WordPress.com deploys them.
- Do not edit theme files through *Appearance → Theme File Editor* or over
  SFTP. The repository is the source of truth; the next deployment overwrites
  anything changed on the server.
- Do not rename or delete the WooCommerce Shop, Cart, Checkout or My Account
  pages. The theme resolves them by ID and links break across the site.
