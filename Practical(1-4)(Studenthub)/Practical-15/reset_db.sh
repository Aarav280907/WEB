#!/usr/bin/env bash
# StudentHub - (re)create the whole database from the SQL files, in dependency order.
# Usage:  ./reset_db.sh [mysql-user] [extra mysql args]      e.g.  ./reset_db.sh root
# WARNING: drops and recreates every StudentHub table.
set -euo pipefail
cd "$(dirname "$0")/.."
USER_="${1:-root}"; shift || true
EXTRA=("$@")
run() { echo ">> $1"; mysql -u"$USER_" "${EXTRA[@]}" < "$1"; }
run Practical-8/schema.sql            # students, events, registrations
run Practical-8/seed.sql              # 22 students, 18 events, 34 registrations
run Practical-8/stored_procedures.sql
run Practical-9/users.sql             # users + audit_logs (+ demo accounts)
run Practical-10/sessions.sql         # remember_tokens
run Practical-12/sample_posters.sql   # attaches the sample poster files
echo "Done. Demo logins: admin@charusat.edu.in / Admin@123   |   25dce072@charusat.edu.in / Student@123"
