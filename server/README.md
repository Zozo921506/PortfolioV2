========================= Installation et lancement ReactJs ===================================
Vous devez tout d'abord vous mettre dans le dossier client
cd /nom_de_votre_dossier/client

Une fois dans le dossier client vous devez installer tout ce que react à besoin pour fonctionner
npm install

Maintenant que les dépendances sont installées il ne reste plus qu'a tester que react se lance bien
npm start

Si tout c'est bien passé et qu'il n'y a pas d'erreur vous devrez avoir react ouvert sur le port 3000
localhost:3000 (dans un navigateur)

Il n'y a plus rien à configurer pour react

========================= Installation et lancement Symfony ===================================
Maintenant que le front end (react) est installé il faut faire pareil avec symfony
Tout d'abord allons dans le dossier server
cd /nom_de_votre_dossier/server

Dans le dossier server il faut installer ce qu'a besoin symfony
composer install

S'il n'y a pas d'erreur les installations se sont bien effectués dans le cas contraire essayé de mettre à jour les paquets
composer update
composer install

Maintenant que les paquets sont bien installés il nous manque une étape avant de lancer le server
Il faut créer un .env et mettre ses variables
APP_ENV="dev" remplacer dev par prod en production
DATABASE_URL="mysql://user:password@127.0.0.1:3306/db_name?serverVersion=8.0.32&charset=utf8mb4" modifier mysql si autre bdd
MAILER_DSN=smtp://user:pass@smtp.example.com:port
MESSENGER_TRANSPORT_DSN="doctrine://default?auto_setup=0"
LOCK_DSN=flock
==================== Pour générer les clés publique et secrete ====================================
==================== php bin/console lexik:jwt:generate-keypair ===================================
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_sel_jwt

Maintenant que vous avez définis vos variables environnement il est temps de lancer le serveur
symfony server:start

Si tout c'est bien passé le server symfony doit maintenant être ouvert sur le port 8000
Vous devriez voir l'écran par défaut de symfony avec un 
localhost:8000 (dans un navigateur)