# Isolated agent development

## Purpose

The AI server and Cursor use separate working copies of this existing repository. An agent proposes changes on `agent/<task>`; a person reviews and merges them. The running WordPress installation is not a development workspace.

## First integration test

This branch adds development documentation only. No PHP, JavaScript, CSS, plugin archive or runtime configuration is changed.

The server has a separate worker account without GitHub credentials or sudo rights. A separate publishing account holds the GitHub login. The initial publisher accepts only these two documentation paths and creates a new `agent/` branch. It refuses replacement of an existing branch. This is a documentation pilot, not a general coding-agent integration.

Before expanding publishing to application code, add disposable test execution, policy checks and an authenticated job interface. Do not give the worker the publishing account's token.

## Checks

1. Inspect the staged diff and reject secret material and unexpected paths.
2. Run whitespace checks and the publishing policy tests.
3. For PHP changes, run syntax validation and suitable WordPress integration tests in an isolated environment.
4. For JavaScript changes, validate syntax and test the affected interface.
5. Commit and publish the task branch; review the diff before merging.
6. Treat deployment as a separate operation.

The initial server inspection found no automated project test suite. PHP and Node.js were not installed on the server host. Therefore no plugin runtime test is claimed by this documentation-only pilot.

## Cursor

Fetch the task branch in your own clone, inspect the changes, and resolve conflicts there. Do not share a live working directory with the server agent.
