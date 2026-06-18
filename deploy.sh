#!/usr/bin/env bash
# Gegaard portaal -> GitHub Pages
# Gebruik:  vul GH_USER en REPO in, daarna:  bash deploy.sh
set -e

GH_USER="JOUW-GEBRUIKERSNAAM"     # <-- invullen
REPO="gegaard-portaal"            # <-- repo-naam (mag je aanpassen)
BRANCH="main"

# 1) repo-map klaarzetten
mkdir -p "$REPO" && cd "$REPO"
cp ../index.html .
cp ../README.md . 2>/dev/null || true

# 2) git init (eerste keer)
if [ ! -d .git ]; then
  git init -b "$BRANCH"
  git remote add origin "https://github.com/$GH_USER/$REPO.git"
fi

# 3) committen en pushen
git add index.html README.md
git commit -m "Update gegaard portaal ($(date +%Y-%m-%d))" || echo "Geen wijzigingen."
git push -u origin "$BRANCH"

echo ""
echo "Klaar. Zet daarna eenmalig Pages aan:"
echo "  Settings -> Pages -> Branch: $BRANCH / root -> Save"
echo "  URL wordt: https://$GH_USER.github.io/$REPO/"
