# Guide des scripts
### Le dossier `scripts` contient plusieurs scripts pour faciliter le développement, les tests et la mise à jour de l'application.

## `starter.bat`
Lance le serveur PHP intégré pour l'application (en local).

**Utilisation :**
```bat
./scripts/starter.bat
```

---

## `tests_unitaires.bat`
Analyse statique du code via PHPStan.

**Utilisation :**
```bat
./scripts/tests_unitaires.bat
```

---

## `phpunit.bat`
Exécute les tests unitaires via PHPUnit.
**Utilisation :**
```bat
./scripts/phpunit.bat
```

---
## `repo_maj.bat`
Met à jour le projet depuis la branche distante (prod), installe les dépendances, applique les migrations et vide le cache.

**Utilisation :**
```bat
./scripts/repo_maj.bat
```



