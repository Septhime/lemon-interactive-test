# Gestionnaire d'Événements - Lemon Interactive Test

Application web de gestion d'événements développée avec Symfony 7.3 et PHP 8.3+.

## Table des matières

- [Installation](#installation)
- [Lancement](#lancement)
- [Architecture & Choix de conception](#architecture--choix-de-conception)
- [Limitations](#limitations)

---

## Installation

### Via Docker (recommandé)

#### 1. Cloner le projet

```bash
git clone https://github.com/Septhime/lemon-interactive-test.git
cd lemon-interactive-test
```

#### 2. Construire les conteneurs

```bash
docker compose -f compose.yaml -f compose.prod.yaml build --pull --no-cache
```

#### 3. Démarrer les conteneurs

```bash
docker compose up --wait
```

#### 4. Charger des données de test

```bash
docker compose exec php bin/console doctrine:fixtures:load
```

### Via un environnement local

#### 1. Cloner le projet

```bash
git clone https://github.com/Septhime/lemon-interactive-test.git
cd lemon-interactive-test
```

#### 2. Installer les dépendances

```bash
composer install
```

#### 3. Configurer l'environnement

Créer un fichier `.env.local` à la racine du projet :

```bash
cp .env .env.local
```

Configurer la connexion à la base de données dans `.env.local` :

```env
# Exemple avec MySQL
DATABASE_URL="mysql://username:password@127.0.0.1:3306/event_manager?serverVersion=8.0"

# Exemple avec PostgreSQL
# DATABASE_URL="postgresql://username:password@127.0.0.1:5432/event_manager?serverVersion=16&charset=utf8"

# Exemple avec SQLite
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

#### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Compiler les assets

```bash
php bin/console asset-map:compile
```

#### 6. Charger des données de test

```bash
php bin/console doctrine:fixtures:load
```

---

## Lancement

### Docker

Le site est accessible sur `https://localhost:80`

### Développement (Symfony CLI)

```bash
symfony server:start
```

L'application sera accessible sur `https://127.0.0.1:8000`

### Développement (serveur PHP intégré)

```bash
php -S localhost:8000 -t public/
```

L'application sera accessible sur `http://localhost:8000`

---

## Architecture & Choix de conception

### Architecture en couches

Le projet suit une architecture avec séparation des responsabilités :

```
Controller → Service → Repository → Entity
```

#### Séparation des services métier
 
  L'application utilise 3 services distincts au lieu d'un seul service monolithique :
  
  - **EventManagementService** : Création, modification, suppression d'événements
  - **EventSubscriptionService** : Inscription/désinscription des participants
  - **EventAuthorizationService** : Contrôle des permissions et autorisations

Cela permet une meilleure organisation du code, facilite les tests unitaires, permet de réutiliser les services et améliore la maintenabilité.

#### Validation côté serveur avec Symfony Form
Utilisation de Symfony Forms (`EventType`, `UserRegistrationType`) pour :
- Validation automatique des données
- Protection CSRF intégrée
- Rendu automatique des formulaires
- Gestion des erreurs unifiée

Permet d'avoir une validation robuste et respecte les conventions Symfony.

#### Utilisation d'AssetMapper

L'application utilise AssetMapper pour gérer les assets (CSS, JS) :
- Compilation et minification des fichiers
- Gestion des dépendances
- Meilleure performance en production

Le projet necessite qu'un fichier CSS (Bootstrap 5), il n'est donc pas nécessaire d'utiliser un gestionnaire de paquets comme npm ou yarn.

---

## Limitations

1. **Pas de pagination**
   - Tous les événements sont affichés sur une seule page
   - Peut poser problème avec un grand nombre d'événements

2. **Pas de gestion des rôles avancés**
   - Un seul rôle utilisateur (`ROLE_USER`)
   - Pas d'administrateur ou de modérateur

3. **Filtrage limité**
   - Filtrage uniquement par dates
   - Pas de recherche par titre, lieu ou organisateur

4. **Gestion des capacités**
   - Pas de limite du nombre de participants
   - Pas de liste d'attente
