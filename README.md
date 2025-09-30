# Nebula

Nebula est un projet communautaire pour les passionnés d'astronomie, de fuséologie et de tout ce qui touche à l'espace.
Il permet de retrouver les actualités, les lancements, des documents et de rassembler tous les passionnés au même endroit.

---

## Installation

1. Cloner le projet :

```bash
git clone https://github.com/MatheoPUEL/Nebula
cd Nebula
```

2. Construire et lancer les containers Docker :

```bash
docker compose build --pull --no-cache
docker compose up -d
```

3. Entrer dans le container PHP

```bash
docker exec -it php-nebula-1 /bin/bash
```
4. Installer les dépendances

```bash
composer install
```
5. Lancer le worker Messenger (Utile seulement si vous voulez envoyer des mails)

```bash
php bin/console messenger:consume async -vv
```

6. Accéder au projet :

| Service           | URL                                                      |
|-------------------|----------------------------------------------------------|
| Application web   | [https://localhost](https://localhost)                  |
| PhpMyAdmin        | [https://localhost:8080](https://localhost:8080)        |
| Mailpit           | [http://localhost:8025/](http://localhost:8025/)        |

---

## Commandes utiles

### Arrêter le projet

```bash
docker compose down --remove-orphans
```

### Lancer les tests

```bash
APP_ENV=test php bin/phpunit
```

### Lancer l'importation des APOD

```bash
php -d memory_limit=-1 bin/console app:import-apod
```


---

## Contribuer

1. Fork le projet
2. Crée une branche pour ta fonctionnalité (`git checkout -b feature/ma-fonctionnalité`)
3. Commit tes changements (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Push sur ta branche (`git push origin feature/ma-fonctionnalité`)
5. Ouvre une Pull Request sur le projet principal

---

Bonne exploration de l’espace ! 🚀
