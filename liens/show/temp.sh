#!/bin/bash

# Vérifie que temp.txt existe
if [ ! -f "temp.txt" ]; then
    echo "❌ Le fichier temp.txt n'existe pas dans le dossier actuel."
    exit 1
fi

# Parcours des répertoires du dossier courant (pas récursif)
for dir in */ ; do
    # Vérifie que c'est bien un répertoire
    [ -d "$dir" ] || continue

    # Vérifie s'il contient exactement un fichier index.php (et pas dans des sous-sous-dossiers)
    count=$(find "$dir" -maxdepth 1 -type f -name "index.php" | wc -l)

    if [ "$count" -eq 1 ]; then
        echo "✅ Remplacement de $dir/index.php"
        cp temp.txt "$dir/index.php"
    fi
done
