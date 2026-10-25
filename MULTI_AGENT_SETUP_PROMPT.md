# Multi-Agent Coordination & Replication Prompt

This document provides a turnkey prompt and setup guide to replicate the multi-agent parallel worktree system, `.agent-states` coordination protocol, `composer clean:agent` command, and `/shipit` slash command into any other Laravel project.

---

## Turnkey Replication Prompt
*Copy and paste the block below directly into a Gemini CLI session in your other Laravel repository:*

```markdown
You are tasked with setting up the Multi-Agent Parallel Worktree Coordination & Shipping System in this Laravel application, replicating the architecture used in https://github.com/jplataquin/mqms.

If needed, you can reference the implementation directly from GitHub:
- Repository: https://github.com/jplataquin/mqms
- Reference Files:
  - `composer.json` (scripts: `agents:clean`)
  - `GEMINI.md` (`## Parallel Agent Coordination` section)
  - `.gitignore` (`.agent-states` rule)

Please implement the following 5 components into this repository:

---

### Step 1: Add Worktree & Agent Cleanup Scripts to `composer.json`
Inspect `composer.json` and add the following scripts to the `"scripts"` section:
```json
"scripts": {
    "agents:clean": "git worktree list --porcelain | grep '^worktree ' | tail -n +2 | cut -d' ' -f2- | xargs -r -I {} git worktree remove --force \"{}\" && git worktree prune && git branch --list 'worktree-*' | xargs -r git branch -D && rm -rf .agent-states/*",
    "clean:agent": "@composer agents:clean",
    "clean:agents": "@composer agents:clean"
}
```
*Purpose: Allows running `composer agents:clean` or `composer clean:agent` to force-remove all auxiliary worktrees, prune dead git metadata, delete temporary `worktree-*` branches, and wipe `.agent-states`.*

---

### Step 2: Update `.gitignore`
Append `.agent-states` to `.gitignore` so agent state files and symlinks are never tracked in version control:
```gitignore
.agent-states
.agent-states/
```

---

### Step 3: Embed Multi-Agent Protocol in `GEMINI.md`
Ensure `GEMINI.md` exists at the project root (create it or append to it if it already exists). Add the following coordination section:

```markdown
## Parallel Agent Coordination
You are operating in a multi-agent environment where multiple Gemini sessions run in parallel git worktrees created via `gemini -w <name>`. To prevent merge conflicts and optimize work across concurrent agents, follow this coordination and shipping protocol:

1. **Self-Identification & Setup**:
   - Determine your agent ID from your workspace directory name (e.g., if working in `.gemini/worktrees/task-1`, your ID is `task-1`).
   - Automatically ensure the shared state folder is symlinked by running:
     `mkdir -p ../../.agent-states && ln -sfn ../../.agent-states .agent-states`
   - Your dedicated state file is `.agent-states/<your_agent_id>.md`.

2. **Heartbeat & Broadcasting**:
   - As soon as you define your plan or change intent, write/update your state file with:
     ```markdown
     # Agent: <your_agent_id>
     - **Status**: Active
     - **Updated At**: YYYY-MM-DD HH:MM UTC
     - **Current Intent / Task**: <brief description of what you are doing>
     - **Components Modified**:
       - <file or component 1>
       - <file or component 2>
     ```

3. **Dynamic Conflict Checking**:
   - Before executing code edits (`replace`, `write_file`, etc.), inspect all other files in `.agent-states/`.
   - Ignore your own state file.
   - Ignore state files with `Status: Idle` or with an `Updated At` older than 30 minutes (stale).
   - If an active agent lists overlapping files or components in `Components Modified`, adjust your plan, wait, or ask the user before proceeding.

4. **Automated Completion & Shipping Workflow (Ship to Main)**:
   Once your task is completed and verified, automatically ship your changes to `main`:
   a. **Descriptive Commit**:
      - Stage changed files: `git add <files>` (avoid committing untracked environment or temporary files).
      - Commit with a clear, descriptive message: `git commit -m "<type>(<scope>): <concise description of work>"`.
   b. **Sync with Main**:
      - Fetch latest remote changes: `git fetch origin main`
      - Merge main into your branch: `git merge origin/main`
   c. **Reconcile Conflicts (if any)**:
      - If conflict markers exist (`<<<<<<<`, `=======`, `>>>>>>>`), inspect and intelligently merge the code so neither your changes nor incoming changes are lost.
      - Perform syntax checks on touched files (e.g., `php -l <file>`).
      - Stage and commit the merge: `git commit -m "chore: merge origin/main and resolve conflicts"`
   d. **Direct Push to Main**:
      - Push your branch directly to the remote main branch: `git -c credential.helper=store push origin HEAD:main`
   e. **Clean State**:
      - Mark your state file `.agent-states/<your_agent_id>.md` as `Status: Idle` or remove it.
      - Announce: `🚀 Task successfully combined and shipped directly to main!`
```

---

### Step 4: Configure Global `/shipit` Slash Command in `~/.gemini/commands.toml`
Check if `~/.gemini/commands.toml` exists. If not, create it. Add or ensure the `[shipit]` table is present:

```toml
[shipit]
description = "Parallel AI Sync: Pull, auto-resolve conflicts, and direct push to main"
prompt = """
You are tasked with safely shipping current workspace changes to the main repository.
Execute these steps exactly:
1. Check git status. If there are modified or untracked changes (excluding untracked environment files), stage them: 'git add <files>' and commit with a descriptive message: 'git commit -m "<type>(<scope>): <summary of changes>"'.
2. Run 'git fetch origin main'.
3. Run 'git merge origin/main' to pull down recent merges from other parallel AI sessions.
4. IF THERE ARE MERGE CONFLICTS:
   - Carefully inspect files with conflict markers (<<<<<<<, =======, >>>>>>>).
   - Intelligently combine the code blocks so no features or logic are lost.
   - Cleanly delete all conflict markers and run syntax checks (e.g. php -l <file>).
   - Run 'git add .' and 'git commit -m "chore: auto-resolved parallel merge conflicts"'.
5. Run 'git push origin HEAD:main' to push the clean changes directly to main.
6. If an agent state file exists under .agent-states/, mark it as Status: Idle or remove it.
7. Print: '🚀 Task successfully combined and shipped directly to main!'
"""
```

---

### Step 5: Initialize Workspace Folder & Verification
1. Create the project `.agent-states` directory: `mkdir -p .agent-states`
2. Validate composer syntax: `composer validate --no-check-all --no-check-publish`
3. Confirm git status is clean of untracked `.agent-states`.
```

---

## Architectural Breakdown

### 1. Worktree Cleanup via Composer (`composer clean:agent`)
```bash
git worktree list --porcelain | grep '^worktree ' | tail -n +2 | cut -d' ' -f2- | xargs -r -I {} git worktree remove --force "{}" && git worktree prune && git branch --list 'worktree-*' | xargs -r git branch -D && rm -rf .agent-states/*
```
- **`tail -n +2`**: Preserves the primary root worktree while pruning all spawned subtrees.
- **`git branch --list 'worktree-*'`**: Cleans up orphan git branches generated by `gemini -w <name>`.
- **`rm -rf .agent-states/*`**: Clears lingering state files across all completed tasks.

### 2. Multi-Agent Shared State Symlink
When Gemini creates a worktree at `.gemini/worktrees/<task-name>`, it resides two levels down from the project root. The command:
```bash
mkdir -p ../../.agent-states && ln -sfn ../../.agent-states .agent-states
```
links `.agent-states` in every worktree directly to the root `.agent-states/`. This enables:
- Real-time heartbeat tracking (`task-1.md`, `task-2.md`, etc.).
- Cross-agent conflict detection prior to running edits (`replace` / `write_file`).
- Automatic back-off when another active agent is modifying the same files.

### 3. Global `/shipit` Command
Located in `~/.gemini/commands.toml`. Because this file is located in the user's home directory (`~/.gemini/`), any workspace opened with Gemini CLI inherits the `/shipit` slash command automatically.
