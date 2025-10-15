# Configuration du Code Coverage avec Xdebug

## Installation de Xdebug sur macOS

### 1. Installer Xdebug via PECL

```bash
# Vérifier la version de PHP
php -v

# Installer Xdebug
pecl install xdebug

# Si vous avez des erreurs, essayez avec sudo
sudo pecl install xdebug
```

### 2. Activer Xdebug dans php.ini

```bash
# Trouver le fichier php.ini
php --ini

# Éditer le fichier php.ini (ou créer un fichier xdebug.ini dans conf.d/)
# Ajouter ces lignes :
```

Contenu à ajouter dans `php.ini` ou `/opt/homebrew/etc/php/8.4/conf.d/ext-xdebug.ini` :

```ini
[xdebug]
zend_extension="xdebug.so"
xdebug.mode=coverage,debug
xdebug.start_with_request=yes
```

### 3. Vérifier l'installation

```bash
php -v
# Devrait afficher "with Xdebug v3.x.x"

php -m | grep xdebug
# Devrait afficher "xdebug"
```

### 4. Redémarrer PHP-FPM (si vous utilisez Valet)

```bash
valet restart
```

## Utilisation avec Pest

### Générer un rapport de couverture HTML

```bash
./vendor/bin/pest --coverage --coverage-html coverage
```

### Générer un rapport de couverture texte

```bash
./vendor/bin/pest --coverage
```

### Avec un seuil minimum de couverture

```bash
./vendor/bin/pest --coverage --min=80
```

### En mode parallèle avec couverture

```bash
./vendor/bin/pest --parallel --coverage
```

## Configuration dans phpunit.xml

Ajoutez cette section dans votre `phpunit.xml.dist` :

```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <exclude>
        <directory>src/Testing</directory>
        <file>src/BlogrServiceProvider.php</file>
    </exclude>
    <report>
        <html outputDirectory="coverage-report"/>
        <text outputFile="php://stdout" showUncoveredFiles="true"/>
    </report>
</coverage>
```

## Alternative : PCOV (plus rapide que Xdebug)

PCOV est plus rapide pour le code coverage que Xdebug :

```bash
# Installer PCOV
pecl install pcov

# Configurer dans php.ini ou conf.d/pcov.ini
[pcov]
extension=pcov.so
pcov.enabled=1
pcov.directory=/chemin/vers/votre/projet/src
```

Ensuite, désactivez Xdebug pour le coverage et utilisez PCOV :

```bash
# Désactiver Xdebug temporairement
php -d xdebug.mode=off ./vendor/bin/pest --coverage
```

## Dépannage

### "No code coverage driver available"

- Vérifiez que Xdebug ou PCOV est installé : `php -m | grep -E 'xdebug|pcov'`
- Vérifiez la configuration : `php -i | grep -E 'xdebug|pcov'`
- Vérifiez que `xdebug.mode` inclut `coverage`

### Xdebug ralentit tout

- Utilisez PCOV à la place pour le coverage
- Ou désactivez Xdebug sauf pour les tests :
  
```bash
# Créer un alias dans ~/.zshrc
alias php-no-xdebug="php -d xdebug.mode=off"
alias pest="php -d xdebug.mode=off ./vendor/bin/pest"
```

### Permission denied lors de l'installation

```bash
# Utiliser sudo
sudo pecl install xdebug

# Ou installer via Homebrew
brew install php@8.4
brew install php@8.4-xdebug
```

## Commandes utiles

```bash
# Vérifier la version de Xdebug
php -v

# Lister les modules PHP
php -m

# Info complète sur Xdebug
php -i | grep xdebug

# Tester Xdebug
php -r "var_dump(extension_loaded('xdebug'));"

# Générer un rapport de couverture complet
./vendor/bin/pest --coverage --coverage-html=coverage --coverage-clover=coverage.xml
```
