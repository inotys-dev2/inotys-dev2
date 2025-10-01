# 🕊️ Obsek.fr

Application Laravel pour la gestion des cérémonies funéraires : utilisateurs multi-rôles (**admin**, **entreprise funéraire**, **officiant**), gestion des paroisses, créneaux de disponibilités, réservations, notifications et paiements.

---

## 🚀 Installation locale avec Laragon

### 1. Cloner le projet
```bash
  git clone https://github.com/inotys-dev2/inotys-dev2.git
  cd inotys-dev2/obsek
```

### 2. Installer les dépendances PHP
> ⚠️ Assure-toi que l’extension `fileinfo` est activée dans :
> `C:\laragon\bin\php\php-8.x.x\php.ini`
>
> Décommente la ligne :
> ```ini
> extension=fileinfo
> ```

Ensuite, lance :
```bash
  composer install
```

### 3. Installer les dépendances front-end
```bash
  npm install
  npm run dev
```

### 4. Créer le fichier d’environnement
```bash
  cp .env.example .env
```

### 5. Générer la clé de l’application
```bash
  php artisan key:generate
```

### 6. Configurer la base de données
Dans le fichier `.env`, adapte les informations :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=obsek
DB_USERNAME=root
DB_PASSWORD=
```

Crée ensuite la base de données :
```bash
  php artisan migrate
```

### 7. Lancer le serveur

#### Avec Laragon
- Démarre **Laragon**
- Va sur [http://obsek.test](http://obsek.test) si tu as créé un hôte virtuel

#### Ou avec Artisan
```bash
  php artisan serve
```

L’application sera accessible sur :  
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 👥 Rôles utilisateurs

| Rôle           | Accès principal                                                        |
|----------------|------------------------------------------------------------------------|
| **Admin**        | Gestion des utilisateurs, des rôles, des paroisses, officiants, etc. |
| **Entreprise**   | Réservation de cérémonies, gestion de leurs créneaux disponibles     |
| **Officiant**    | Consultation de ses créneaux et des réservations                     |

---

## 📬 Fonctionnalités principales

- ✅ Authentification sécurisée avec **Laravel Breeze**
- ✅ Vérification **par e-mail avec code**
- ✅ Tableau de bord personnalisé selon le rôle
- ✅ Gestion des **cérémonies** (paroisse, date, officiant, entreprise)
- ✅ Notifications automatiques
- ✅ Paiements (à venir)
- ✅ Gestion des disponibilités des officiants
- ✅ Interface propre et intuitive avec **TailwindCSS**

---

## ✉️ Vérification d’e-mail (processus)

1. Lorsqu’un utilisateur s’inscrit, un **code de vérification** est automatiquement généré.
2. Ce code est envoyé par e-mail à l’adresse fournie.
3. L’utilisateur doit saisir le code pour **activer son compte**.
4. Une fois validé, le compte est marqué comme **vérifié** et l’accès complet est accordé.
5. Si le code est incorrect ou expiré, un nouveau peut être renvoyé.

---

## 🧰 Commandes utiles

```bash
# Nettoyer le cache de l'application
  php artisan optimize:clear

# Lancer les migrations avec les seeders
  php artisan migrate --seed

# Recompiler les assets front-end 
  npm run dev

# Mettre le serveur Laravel en route
  php artisan serve
```

---

## 🛠️ Prérequis

- **PHP** >= 8.2
- **Composer**
- **Node.js & NPM**
- **MySQL**
- **Laragon** (recommandé sur Windows)

---

## 🧑‍💻 Stack technique

| Élément          | Technologie utilisée          |
|------------------|------------------------------|
| **Framework**    | [Laravel 11](https://laravel.com) |
| **Starter Kit**  | [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze) |
| **CSS**          | [TailwindCSS](https://tailwindcss.com) |
| **Base de données** | [MySQL](https://www.mysql.com) |
| **Serveur local** | [Laragon](https://laragon.org) |

---

## 📦 Dépôt GitHub

Projet hébergé sur GitHub :  
🔗 [https://github.com/inotys-dev2/inotys-dev2](https://github.com/inotys-dev2/inotys-dev2)

---

## 📄 Licence

Projet privé — © 2025 **Obsek.fr**\
🧠 **Développé par :** Steven Mallochet\
📅 Dernière mise à jour : Octobre 2025
