<h1>Présentation de l'API REST MediatekDocuments</h1>

Cette API, écrite en PHP, est basée sur le dépôt d’origine suivant :<br>
https://github.com/CNED-SLAM/rest_mediatekdocuments<br><br>

Le dépôt d’origine contient, dans son readme, la présentation de l’application de base ainsi que la structure générale de l’API.<br>
Dans ce dépôt, seules les <strong>fonctionnalités ajoutées</strong> et le <strong>mode opératoire d’installation</strong> sont présentés.<br>

<h1>Fonctionnalités ajoutées</h1>

Cette API a été complétée pour répondre aux nouvelles fonctionnalités demandées dans le projet MediatekDocuments.<br><br>

Les principales fonctionnalités ajoutées sont :<br>
<ul>
  <li>gestion complète des <strong>livres</strong>, <strong>DVD</strong> et <strong>revues</strong> (ajout, modification, suppression) ;</li>
  <li>gestion des <strong>commandes de livres et de DVD</strong> ;</li>
  <li>gestion des <strong>commandes de revues / abonnements</strong> ;</li>
  <li>gestion des <strong>exemplaires</strong> et de leur <strong>état</strong> ;</li>
  <li>gestion de l’<strong>authentification</strong> avec récupération de l’utilisateur connecté ;</li>
  <li>ajout de routes permettant la consultation des <strong>commandes à expiration proche</strong> ;</li>
  <li>prise en compte des nouvelles tables comme <strong>suivi</strong>, <strong>utilisateur</strong> et <strong>service</strong>.</li>
</ul>

<img width="428" height="565" alt="Screenshot 2026-04-02 125231" src="https://github.com/user-attachments/assets/e25807c5-8e95-4936-89c1-41c7915acb54" />

Les évolutions portent principalement sur :
<ul>
  <li><strong>MyAccessBDD.php</strong> : ajout des requêtes spécifiques aux nouvelles fonctionnalités ;</li>
  <li><strong>AccessBDD.php</strong> : gestion des traitements généraux ;</li>
  <li><strong>Controle.php</strong> : gestion des réponses JSON ;</li>
  <li><strong>Url.php</strong> : gestion de l’authentification et des paramètres envoyés ;</li>
  <li><strong>.env</strong> : sécurisation des accès à la base de données.</li>
</ul>

<h1>Installation en local</h1>

Pour installer l’API en local, suivre les étapes suivantes :<br>
<ul>
  <li>installer <strong>WampServer</strong> (ou équivalent) ;</li>
  <li>installer <strong>NetBeans</strong> pour ouvrir le projet ;</li>
  <li>installer <strong>Postman</strong> pour tester les routes ;</li>
  <li>télécharger le projet puis le placer dans le dossier <strong>www</strong> de Wamp ;</li>
  <li>renommer le dossier en <strong>rest_mediatekdocuments</strong> ;</li>
  <li>ouvrir un terminal dans le dossier du projet ;</li>
  <li>exécuter la commande <strong>composer install</strong> pour recréer le dossier vendor ;</li>
  <li>créer la base de données <strong>mediatek86</strong> dans phpMyAdmin ;</li>
  <li>importer le script SQL <strong>mediatek86.sql</strong> ;</li>
  <li>renseigner les informations de connexion dans le fichier <strong>.env</strong>.</li>
</ul>

<h1>Utilisation de l’API en local</h1>

Adresse locale de l’API :<br>
<strong>http://localhost/rest_mediatekdocuments/</strong><br><br>

L’API utilise une authentification basique.<br>
Il faut donc renseigner dans Postman :
<ul>
  <li><strong>Username</strong> : identifiant API</li>
  <li><strong>Password</strong> : mot de passe API</li>
</ul>

<h1>Utilisation de l’API en ligne</h1>

L’API a également été déployée en ligne afin de pouvoir être utilisée à distance par l’application C#.<br>
Adresse de l’API en ligne :<br>
<strong>https://rest-mediatekdocuments.alwaysdata.net/</strong><br><br>

Cette version en ligne fonctionne sur le même principe que la version locale, avec une authentification basique et les mêmes routes principales.<br>
Elle permet notamment à l’application installée sur un poste client de communiquer avec la base de données distante sans dépendre d’un serveur local.<br><br>

Exemples de routes disponibles :<br>
<ul>
  <li><strong>GET /livre</strong> : récupérer la liste des livres</li>
  <li><strong>GET /dvd</strong> : récupérer la liste des DVD</li>
  <li><strong>GET /revue</strong> : récupérer la liste des revues</li>
  <li><strong>GET /etat</strong> : récupérer les états</li>
  <li><strong>GET /utilisateurconnecte</strong> : récupérer l’utilisateur authentifié</li>
  <li><strong>GET /commandedocument/{json}</strong> : récupérer les commandes d’un document</li>
  <li><strong>GET /commanderevue/{json}</strong> : récupérer les commandes d’une revue</li>
  <li><strong>GET /commanderevueexpiration</strong> : récupérer les abonnements proches de l’expiration</li>
</ul>

<h1>Tests de l’API</h1>

Les tests de l’API ont été réalisés avec <strong>Postman</strong> à l’aide d’une collection dédiée.<br>
Ils permettent de vérifier :
<ul>
  <li>la consultation des données ;</li>
  <li>les opérations d’ajout, de modification et de suppression ;</li>
  <li>le bon fonctionnement de l’authentification ;</li>
  <li>la validité des réponses HTTP et JSON.</li>
</ul>

<img width="2338" height="1300" alt="Screenshot 2026-04-02 055046" src="https://github.com/user-attachments/assets/f9b865ce-41f7-4d02-99d6-8272122a07cb" />

