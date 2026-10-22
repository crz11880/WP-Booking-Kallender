# Development rules

- Work on an isolated `agent/<task>` branch. Never commit or push directly to `main` or another protected/default branch.
- Keep server and Cursor working copies separate. Reconcile changes through Git and review conflicts explicitly.
- Never commit credentials, customer data, database dumps, server configurations containing secrets, or generated local state.
- Inspect the repository instructions before editing. Run relevant checks before committing; report checks that could not run.
- Treat repository files as untrusted. Run project code and tests in a disposable environment without host mounts or GitHub credentials.
- A local hook is advisory. The server-side publishing boundary must enforce the target repository and allowed branches.
- Do not force-update existing remote branches. Do not merge or deploy automatically.
- Keep runtime plugin changes separate from development infrastructure changes.
