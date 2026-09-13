# Votre Para — Analyse concurrentielle et cahier des charges e-commerce

**Version :** 1.0
**Date de recherche :** 13 septembre 2026
**Marché :** parapharmacie en ligne en Tunisie
**Projet :** Votre Para

---

## 1. Objectif du document

Ce document transforme une première vitrine WordPress/WooCommerce en plan concret pour une vraie boutique en ligne. Il rassemble :

- une analyse de plusieurs concurrents tunisiens ;
- les fonctions attendues par les clients du marché ;
- les opportunités de différenciation pour Votre Para ;
- un périmètre MVP priorisé ;
- les exigences fonctionnelles, opérationnelles et techniques ;
- une feuille de route exploitable pour le développement.

Ce document n'est pas une validation juridique. Avant la mise en production, les règles tunisiennes applicables à la vente en ligne, aux produits de parapharmacie, à la protection des données, à la facturation, à la publicité santé et aux paiements doivent être validées avec les professionnels compétents.

## 2. Méthode et limites

L'analyse repose sur les pages publiques accessibles le 13 septembre 2026. Elle examine la navigation, les pages d'accueil, le catalogue, les fiches produits, les promotions et les informations visibles sur les services. Aucun achat réel n'a été effectué : les parcours de paiement, les délais annoncés et les services après-vente n'ont donc pas été testés de bout en bout.

Les fonctionnalités peuvent évoluer. Les affirmations commerciales des concurrents (authenticité, volumes de catalogue, délais, certifications, etc.) sont rapportées comme leurs propres déclarations et non comme des faits indépendamment vérifiés.

## 3. Concurrents étudiés

| Concurrent | Positionnement observé | Fonctions et arguments remarquables | Limites/opportunités visibles |
|---|---|---|---|
| [Para Fendri](https://parafendri.tn/) | Enseigne physique et boutique généraliste, présence Sfax/Tunis | Recherche avec termes populaires, compte, panier latéral, wishlist, comparateur, promotions avec compte à rebours, nouveautés, coffrets, marques, stock chiffré, EAN, recommandations, contact livraison/réclamation | Interface et contenus parfois incohérents en français/anglais ; catégories et libellés chargés ; conditions de livraison complexes ; occasion de proposer une expérience plus simple et mieux éditée |
| [Tunisie Para](https://tunisiepara.com/) | Très grand catalogue et forte logique promotionnelle/SEO | Catégories profondes, marques, ventes flash, produits à emballage défectueux ou date proche, wishlist, blog/conseils, newsletter, livraison gratuite annoncée autour de 99–100 DT, paiement à la livraison, service client | Densité importante et parcours potentiellement chargé ; opportunité pour une sélection plus claire, des conseils plus humains et une meilleure cohérence visuelle |
| [Pharma Shop](https://pharma-shop.tn/) | Acteur à grand volume, prix/promotion et fidélisation | Plus de 7 000 produits annoncés, compte client, achat express, promotions, nouveautés, bons plans, coffrets, avis modérés, points de fidélité convertibles en bon, descriptions/composition, recommandations, blog, application mobile, réclamations | Présentation très commerciale et vaste catalogue ; opportunité pour mieux guider le choix par besoin et expliquer les produits sans surcharger la page |
| [Para Expert](https://www.paraexpert.tn/) | Expertise, authenticité et profondeur de catalogue | Plus de 200 marques et 5 000 produits annoncés, wishlist, aperçu rapide, statuts de disponibilité, catégories par besoin, conseils, paiement à la livraison/en ligne, livraison annoncée 24–72 h, retour annoncé sous 14 jours, avis vérifiés via un tiers | Beaucoup de contenus et de catégories ; différenciation possible par une navigation simplifiée et une preuve d'expertise plus incarnée |
| [Anaïs](https://anais.tn/) | Parapharmacie + institut de beauté/bien-être | Plus de 150 marques annoncées, favoris, comparaison, aperçu rapide, routines, bons cadeaux, promotions, jeux/ventes flash, blog, vidéo, conseil par une équipe spécialisée, réservation/valorisation des soins en institut | Offre extrêmement large ; opportunité pour une identité plus focalisée et un parcours e-commerce plus homogène |
| [Paraflor](https://paraflor.tn/) | Expérience sobre, confiance et livraison nationale | Recherche par produit/marque/besoin, catégories simples, compte, best-sellers, stock, prix unitaire, paiement à la livraison et carte/e-Dinar annoncés, suivi de commande, fidélité, français/arabe, barre mobile, WhatsApp | Référence intéressante pour la clarté ; certaines coordonnées visibles semblent encore génériques, donc les éléments de confiance doivent être réels et vérifiables chez Votre Para |

### Sources complémentaires

- [Douxprix](https://www.douxprix.com/) montre que la comparaison des prix entre parapharmacies tunisiennes est déjà un comportement facilité par des services tiers.
- [Parashop Tunisie](https://www.parashop.tn/) met en avant la recherche par besoin, les prix accessibles et la livraison nationale.
- [Eden Pharma](https://edenpharma.tn/) insiste sur l'authenticité, la sélection et le conseil local.
- [Para du Bonheur](https://paradubonheur.tn/) développe particulièrement le discours sur la provenance, le stockage et l'authenticité.

## 4. Enseignements du marché

### 4.1 Les fonctions devenues standards

Un site crédible doit au minimum proposer :

- recherche visible sur mobile et ordinateur ;
- catalogue organisé par catégories, besoins et marques ;
- filtres et tri efficaces ;
- prix en TND, prix barré et remise lisibles ;
- état du stock ;
- panier persistant ;
- achat sans obligation de créer un compte ;
- paiement à la livraison ;
- livraison partout en Tunisie avec coût et délai transparents ;
- compte client et historique de commandes ;
- contact rapide, particulièrement WhatsApp et téléphone ;
- pages livraison, retour, confidentialité et conditions ;
- site responsive et rapide.

### 4.2 Les leviers commerciaux récurrents

- Promotions, ventes flash et compte à rebours.
- Livraison gratuite au-delà d'un seuil, souvent proche de 99–100 DT.
- Coffrets et packs de routine.
- Nouveautés et meilleures ventes.
- Codes promo, bons d'achat et cadeaux.
- Fidélité et newsletter.
- Recommandations de produits associés.
- Avis clients et preuve d'achat.

### 4.3 Les principaux facteurs de confiance

Dans ce secteur, le client ne juge pas uniquement le prix. Il cherche aussi :

- la garantie d'un produit authentique et traçable ;
- une provenance officielle clairement expliquée ;
- des photos et informations fidèles ;
- un stock réel ;
- une identité commerciale identifiable ;
- un numéro, une adresse et des horaires réels ;
- des conseils responsables ;
- une politique de retour et de réclamation accessible ;
- des avis crédibles ;
- un paiement et un traitement des données sécurisés.

### 4.4 L'opportunité pour Votre Para

La différenciation recommandée est : **une sélection de confiance, expliquée simplement, avec un conseil humain adapté au quotidien tunisien.**

Votre Para ne devrait pas chercher à paraître plus grand que les acteurs établis dès le lancement. Une meilleure stratégie consiste à être :

1. plus simple à parcourir ;
2. plus rassurant sur l'authenticité et la disponibilité ;
3. plus utile dans le choix d'une routine ;
4. plus rapide sur mobile ;
5. plus transparent sur la livraison et le service après-vente.

## 5. Proposition de valeur

> Votre Para aide chaque client à trouver des produits parapharmaceutiques authentiques et adaptés à son besoin, avec des explications claires, un conseil humain et une livraison fiable partout en Tunisie.

### Piliers de marque

- **Confiance :** fournisseurs identifiés, informations exactes, stock réel.
- **Clarté :** catégories simples, langage compréhensible, prix total transparent.
- **Conseil :** routines, guides et accès rapide à une personne compétente.
- **Proximité :** besoins tunisiens, WhatsApp, français puis arabe.
- **Soin de l'expérience :** design propre, mobile rapide, checkout court.

## 6. Utilisateurs cibles

### Persona A — La cliente routine visage

Elle connaît son problème (acné, taches, sécheresse), mais pas toujours le bon produit. Elle veut filtrer par type de peau, besoin, actif et budget, puis obtenir une explication claire.

### Persona B — Le client qui rachète un produit précis

Il connaît la marque ou la référence. Il veut une recherche rapide, vérifier le stock et le prix, puis commander en quelques étapes.

### Persona C — Le parent

Il recherche des produits adaptés à l'âge du bébé/enfant et attend des informations de sécurité, d'usage et de disponibilité très lisibles.

### Persona D — Le client conseillé sur WhatsApp

Il hésite entre plusieurs produits. Il veut envoyer sa question ou son panier et reprendre son achat sans perdre sa sélection.

### Persona E — L'équipe de la boutique

Elle doit ajouter les produits, ajuster les stocks et prix, confirmer les commandes, gérer les promotions et répondre aux clients sans dépendre d'un développeur.

## 7. Architecture de l'information

### Navigation principale recommandée

1. Visage
2. Cheveux
3. Corps & hygiène
4. Solaire
5. Bébé & maman
6. Compléments alimentaires
7. Matériel & bien-être, si vendu par le client
8. Marques
9. Promotions
10. Conseils

### Navigation alternative par besoin

- Acné et imperfections
- Taches et éclat
- Peau sèche/sensible
- Anti-âge
- Chute de cheveux
- Pellicules/cuir chevelu
- Protection solaire
- Fatigue et immunité
- Grossesse, maman et bébé

La taxonomie doit éviter de placer un même concept sous plusieurs noms. Chaque produit peut appartenir à une catégorie commerciale, plusieurs besoins et une marque, sans dupliquer la fiche.

## 8. Périmètre MVP — indispensable au lancement

### 8.1 Accueil

- Proposition de valeur et appel à l'action immédiatement visibles.
- Barre de recherche avec suggestions de produits, marques et besoins.
- Catégories principales.
- Produits les plus vendus, nouveautés et promotions.
- Bloc « choisir selon mon besoin ».
- Preuves de confiance : authenticité, livraison, paiement, assistance.
- Marques principales.
- Accès WhatsApp fixe mais non intrusif.
- Témoignages uniquement s'ils sont réels.
- Inscription newsletter avec consentement explicite.

### 8.2 Catalogue et catégories

- Grille responsive.
- Filtres : marque, prix, besoin, type de peau/cheveux, disponibilité, promotion.
- Tri : pertinence, meilleures ventes, nouveauté, prix croissant/décroissant.
- Nombre de résultats et filtres actifs visibles.
- Bouton « effacer les filtres ».
- Pagination ou chargement progressif avec URLs indexables.
- Cartes avec image, marque, nom, format, prix, remise, stock et ajout rapide.
- État « rupture » et possibilité de demander une alerte de retour.

### 8.3 Recherche

- Tolérance aux accents, fautes simples et variations de noms.
- Recherche par nom, marque, SKU/EAN, catégorie, besoin et actif principal.
- Suggestions dès la saisie.
- Historique/recherches populaires uniquement si utiles.
- Page zéro résultat proposant correction, catégories et contact.
- Suivi analytique des recherches sans résultat pour améliorer le catalogue.

### 8.4 Fiche produit

Chaque fiche doit contenir :

- nom normalisé, marque, format/contenance ;
- galerie d'images optimisées et zoom ;
- prix TTC en TND, ancien prix et pourcentage de remise ;
- état de stock fiable ;
- quantité et ajout au panier ;
- bénéfice principal en une phrase ;
- indications et profil concerné ;
- mode d'utilisation ;
- composition/ingrédients lorsque disponible ;
- précautions et avertissements nécessaires ;
- texture, type de peau/cheveux, zone et âge lorsque pertinents ;
- SKU et EAN/GTIN ;
- estimation des frais/délais de livraison ou lien très visible ;
- bouton « demander conseil sur WhatsApp » incluant le nom et l'URL du produit ;
- produits complémentaires cohérents ;
- produits similaires ;
- avis clients modérés et marqués « achat vérifié » lorsque possible ;
- balisage SEO Product, Offer et AggregateRating uniquement avec des données réelles.

Les descriptions médicales improvisées ou promesses de guérison doivent être interdites. Les contenus doivent rester cohérents avec les informations du fabricant et le cadre applicable.

### 8.5 Panier

- Modification des quantités et suppression.
- Mini-panier accessible depuis toutes les pages.
- Prix produit, remise, sous-total, livraison estimée et total lisibles.
- Barre de progression vers la livraison gratuite, si ce seuil existe.
- Code promo.
- Suggestions limitées à 2–4 compléments réellement pertinents.
- Persistance du panier entre les visites.
- Bouton de commande dominant sur mobile.

### 8.6 Checkout tunisien

- Achat invité par défaut ; création de compte proposée après ou pendant la commande.
- Champs minimaux : nom, téléphone, e-mail, adresse, gouvernorat, délégation/localité, code postal facultatif selon les besoins du transporteur, notes.
- Numéro tunisien validé sans bloquer les cas légitimes.
- Sélection du gouvernorat avec frais/délai mis à jour avant confirmation.
- Paiement à la livraison dès le MVP.
- Paiement en ligne seulement après choix et validation du prestataire par le client.
- Récapitulatif complet avant validation.
- Case d'acceptation des conditions, non précochée.
- Page de confirmation avec numéro de commande et prochaines étapes.
- E-mail de confirmation ; SMS/WhatsApp en phase suivante ou dès le MVP si l'intégration est fiable.
- Prévention des doubles commandes et reprise en cas d'erreur.

### 8.7 Compte client

- Inscription, connexion et récupération de mot de passe.
- Historique et détail des commandes.
- Statut de commande.
- Adresses enregistrées.
- Informations personnelles.
- Recommande rapide à partir d'une ancienne commande.
- Suppression/export des données selon les obligations applicables.

### 8.8 Livraison, retours et suivi

- Tableau clair des zones, frais, seuil gratuit et délais indicatifs.
- Numéro ou lien de suivi lorsque le transporteur le permet.
- Statuts internes : nouvelle, à confirmer, confirmée, en préparation, expédiée, livrée, refusée, retournée, annulée, remboursée.
- Politique explicite pour produit endommagé, erreur, colis refusé et produit non retournable pour raisons d'hygiène/sécurité, après validation juridique.
- Formulaire de réclamation avec numéro de commande et photos.

### 8.9 Contact et conseil

- Téléphone, WhatsApp, e-mail, adresse, carte et horaires réels.
- Bouton WhatsApp contextuel depuis produit et panier.
- Messages automatiques précisant les heures de réponse.
- FAQ sur commande, livraison, paiement, authenticité et retours.
- Formulaire de contact protégé contre le spam.

### 8.10 Back-office

- Gestion produits, variations, marques, catégories et attributs.
- Import/export CSV contrôlé.
- Prix normal, prix promo et dates de promotion.
- Stock central avec seuil d'alerte.
- Gestion des commandes et notes internes.
- Impression de facture/bon de préparation selon le processus validé.
- Coupons avec dates, limites, minimum et exclusions.
- Modération des avis.
- Gestion des bannières et blocs d'accueil sans code.
- Comptes employés avec permissions limitées et journal des actions sensibles.

## 9. Phase 2 — croissance

- Wishlist synchronisée au compte.
- Alertes de retour en stock.
- Programme de fidélité simple et compréhensible.
- Packs/routines avec réduction et stock calculé depuis les composants.
- Cadeau selon montant ou produit acheté.
- Paiement en ligne local validé.
- Notifications WhatsApp/SMS transactionnelles avec consentement et modèle approuvé.
- Suivi transporteur automatisé.
- Français et arabe avec interface RTL correcte.
- Quiz de routine non médical : type de peau, besoin, préférence et budget.
- Articles/conseils reliés aux produits.
- Avis vérifiés via invitation après livraison.
- Retrait en magasin si l'organisation physique le permet.
- Abandons de panier avec consentement et règles de fréquence.

## 10. Phase 3 — différenciation avancée

- Comparateur de 2–4 produits sur des attributs utiles, pas seulement le prix.
- Abonnement/réapprovisionnement pour produits consommés régulièrement.
- Programme de parrainage.
- Cartes cadeaux.
- Segmentation CRM par besoin et comportement.
- Recommandations personnalisées responsables.
- Consultation/conseil sur rendez-vous si une personne qualifiée l'assure.
- Application mobile uniquement si les données montrent une forte fréquence de réachat ; une excellente PWA/mobile web est prioritaire.

## 11. Ce qu'il ne faut pas prioriser au lancement

- Une application mobile native.
- Un comparateur complexe.
- Des milliers de catégories vides.
- Un chatbot donnant des conseils de santé non supervisés.
- Des comptes à rebours permanents ou fausses urgences.
- Des avis fictifs.
- Une fidélité difficile à expliquer.
- Plusieurs moyens de paiement non testés.
- Un blog massif avant que les fiches produits, le stock et le checkout soient fiables.

## 12. Modèle de données produit recommandé

| Groupe | Champs |
|---|---|
| Identité | ID, SKU, EAN/GTIN, nom, slug, marque, gamme |
| Vente | prix TTC, prix promo, début/fin promo, coût, taxe, statut |
| Logistique | stock, seuil bas, poids, dimensions, classe de livraison |
| Classification | catégorie, sous-catégorie, besoins, public, âge, type de peau/cheveux, zone |
| Présentation | description courte, description longue, bénéfices, images, vidéo facultative |
| Usage | indications, mode d'emploi, fréquence, durée indicative |
| Formulation | ingrédients/INCI, actifs principaux, texture, parfum, labels justifiés |
| Sécurité | précautions, contre-indications communiquées par le fabricant, avertissements |
| Merchandising | nouveauté, best-seller, badges autorisés, produits associés, ordre |
| SEO | meta title, meta description, image alt, canonical, données structurées |
| Traçabilité interne | fournisseur, référence fournisseur, lot/date si gérés, date de mise à jour |

Une convention de nommage doit être fixée avant l'import : `Marque + Gamme + Produit + Format`. Les marques, contenances et attributs ne doivent pas être saisis sous plusieurs orthographes.

## 13. Promotions et règles commerciales

- Afficher l'ancien prix seulement s'il correspond à une vraie référence de prix conforme.
- Afficher clairement la valeur ou le pourcentage économisé.
- Planifier début et fin de campagne.
- Ne jamais vendre au-delà du stock disponible, sauf précommande explicitement autorisée.
- Prévoir les types : pourcentage, montant fixe, pack, cadeau, livraison gratuite, coupon.
- Définir la compatibilité entre promotions pour éviter le cumul involontaire.
- Mesurer marge et revenu, pas uniquement le nombre de commandes.
- Prévoir une landing page pour chaque campagne saisonnière : solaire, rentrée, Ramadan/Aïd si pertinent, fête des mères, hiver, etc.

## 14. Exigences non fonctionnelles

### Performance

- Approche mobile-first.
- Images WebP/AVIF, tailles responsives et lazy loading hors premier écran.
- Cache pages/objets compatible WooCommerce.
- Limiter plugins, scripts marketing et polices.
- Objectifs Core Web Vitals au 75e percentile : LCP ≤ 2,5 s, INP ≤ 200 ms, CLS ≤ 0,1.
- Surveillance de disponibilité et erreurs checkout.

### Accessibilité

- Navigation clavier complète.
- Contrastes conformes WCAG 2.2 AA.
- Labels réels, messages d'erreur associés aux champs.
- Cibles tactiles confortables.
- Textes alternatifs utiles.
- Respect de `prefers-reduced-motion`.
- Pages et checkout utilisables avec zoom à 200 %.

### Sécurité

- HTTPS partout.
- Mises à jour WordPress/WooCommerce et plugins maîtrisées.
- Sauvegardes quotidiennes hors serveur avec tests de restauration.
- 2FA pour administrateurs.
- Rôles minimaux pour employés.
- Protection brute force, spam, injections et téléversements.
- Aucun secret dans Git.
- Aucun stockage local de données de carte ; passer par un prestataire conforme.
- Journalisation des connexions et changements sensibles.
- Environnement de staging séparé de la production.

### Confidentialité et conformité

- Collecter uniquement les données nécessaires.
- Expliquer finalités, durée de conservation et destinataires.
- Consentement distinct pour marketing ; aucune case précochée.
- Outil de préférences cookies avant activation des traceurs non essentiels.
- Procédure d'accès, correction et suppression.
- Validation juridique des mentions, CGV, retours, facturation et publicité.
- Séparer clairement produits de parapharmacie et toute catégorie dont la vente en ligne serait réglementée ou interdite.

## 15. SEO et contenu

- URLs courtes et stables : `/categorie/visage/`, `/marque/avene/`, `/produit/.../`.
- Un seul H1 par page et hiérarchie logique.
- Titres/meta descriptions uniques.
- Canonicals pour filtres et variations.
- Sitemap produits, catégories, pages et articles.
- Données structurées Organization, BreadcrumbList, Product et Article.
- Ne jamais baliser un avis ou prix absent de la page.
- Pages catégories avec texte utile, concis et propre au besoin tunisien.
- Guides répondant aux vraies questions de recherche, relus par une personne compétente.
- Maillage entre guide, besoin, catégorie et produits.
- Redirections lors des suppressions/remplacements de produits.

## 16. Mesure et indicateurs

### Événements à suivre

- recherche et recherche sans résultat ;
- vue catégorie et utilisation des filtres ;
- vue produit ;
- clic WhatsApp depuis produit/panier ;
- ajout/retrait panier ;
- début checkout ;
- erreur checkout ;
- choix livraison/paiement ;
- commande confirmée ;
- coupon appliqué/refusé ;
- inscription newsletter ;
- demande de retour en stock.

### KPI commerciaux et opérationnels

- taux de conversion global et mobile ;
- ajout panier → commande ;
- abandon checkout par étape ;
- panier moyen ;
- revenu par visite ;
- taux de commandes confirmées, livrées, refusées et retournées ;
- délai moyen de confirmation, préparation et livraison ;
- taux de rupture ;
- taux de réachat à 30/60/90 jours ;
- recherche sans résultat ;
- marge après promotion et livraison ;
- tickets SAV par 100 commandes.

## 17. Critères d'acceptation MVP

Le MVP est prêt à recevoir de vraies commandes seulement si :

1. un client peut trouver un produit par recherche, catégorie, marque et besoin ;
2. les prix, promotions et stocks correspondent au back-office ;
3. une commande invitée peut être passée sur mobile sans erreur ;
4. les frais et délais sont visibles avant la confirmation ;
5. le commerçant reçoit la commande et le client reçoit une confirmation ;
6. une commande ne peut pas être créée deux fois par un double clic ;
7. le stock est décrémenté selon une règle définie et restauré lors d'une annulation pertinente ;
8. les statuts de commande et responsabilités internes sont documentés ;
9. les pages légales et coordonnées réelles sont publiées ;
10. sauvegarde et restauration ont été testées ;
11. aucune donnée ou image de démonstration ne reste visible ;
12. les secrets de développement et identifiants par défaut ont été remplacés ;
13. une commande complète a été testée sur iPhone/Safari, Android/Chrome et ordinateur ;
14. les principales pages passent une vérification accessibilité, performance et SEO ;
15. l'équipe sait ajouter un produit, traiter une commande, modifier un stock et gérer une réclamation.

## 18. Roadmap recommandée

### Étape 0 — décisions métier

- Valider nom, identité, domaine et coordonnées.
- Définir catalogue de lancement et fournisseurs.
- Fixer zones, tarifs, transporteur et seuil gratuit.
- Choisir moyens de paiement.
- Écrire le processus de confirmation et traitement des commandes.
- Valider conditions de vente/retour et responsabilités de contenu.

### Étape 1 — fondations

- Mettre le projet sous Git et séparer développement/staging/production.
- Sécuriser WordPress et les secrets.
- Finaliser taxonomie et modèle produit.
- Construire composants globaux, navigation, recherche et footer.

### Étape 2 — parcours d'achat

- Catalogue, filtres et recherche.
- Fiche produit.
- Panier et checkout invité.
- Livraison et paiement à la livraison.
- E-mails, compte et statuts de commande.

### Étape 3 — contenu et opérations

- Import du vrai catalogue avec contrôle qualité.
- Pages confiance, aide et légales.
- Formation back-office.
- Analytics, sauvegardes et monitoring.

### Étape 4 — recette et lancement

- Tests fonctionnels, appareils, performance, accessibilité et sécurité.
- Commandes pilotes réelles avec cercle restreint.
- Correction des incidents et validation du processus livraison/SAV.
- Lancement progressif, puis observation quotidienne.

### Étape 5 — croissance mesurée

- Ajouter wishlist, retour en stock, fidélité et packs selon les données.
- Produire guides et pages besoins.
- Tester seuil de livraison, offres et recommandations.
- Ajouter arabe, paiement en ligne et automatisations après stabilisation.

## 19. État du projet actuel et écarts

Le dépôt contient déjà :

- une maquette HTML/CSS/JavaScript ;
- un thème WordPress personnalisé ;
- une page d'accueil connectée à WooCommerce ;
- des cartes produits et liens d'ajout au panier ;
- un environnement Docker ;
- des scripts de catalogue et images de démonstration.

Les écarts principaux avant une vraie boutique sont :

- le dossier du projet n'est pas encore suivi par Git ;
- les données sont de démonstration ;
- la recherche, les filtres, les fiches produits et le checkout n'ont pas été conçus/validés comme parcours complet ;
- les formulaires newsletter sont seulement visuels ;
- les coordonnées, liens sociaux et WhatsApp ne sont pas réels ;
- livraison, paiement, taxes, e-mails, retours et SAV ne sont pas configurés ;
- sécurité, sauvegardes, analytics et conformité restent à mettre en place ;
- l'environnement Docker n'était pas actif lors de cette analyse, donc l'état de la base WordPress n'a pas été vérifié.

## 20. Décisions à obtenir du client avant le sprint 1

1. Quel est le nom commercial final et quel domaine sera utilisé ?
2. La boutique a-t-elle un ou plusieurs points de vente et stocks ?
3. Quel est le nombre de produits au lancement ?
4. Quelles catégories et marques sont réellement disponibles ?
5. Qui fournit et valide les photos, descriptions, ingrédients et précautions ?
6. Quel transporteur, quels tarifs par gouvernorat et quel seuil gratuit ?
7. Paiement à la livraison uniquement au début, ou paiement en ligne également ?
8. Qui confirme les commandes et dans quels horaires ?
9. Quel numéro WhatsApp et quel délai de réponse promis ?
10. Quelle politique pour colis refusés, produits endommagés et retours ?
11. Le site doit-il être français seulement au lancement ou français/arabe ?
12. Le stock en ligne est-il partagé avec la boutique physique ?
13. Quel budget mensuel pour hébergement, outils, SMS/WhatsApp, e-mail et maintenance ?
14. Qui est responsable de la validation juridique et des contenus santé ?

## 21. Recommandation finale

Commencer par une boutique mobile rapide avec 100 à 500 références propres, un catalogue très bien classé, des fiches fiables, un checkout invité court, le paiement à la livraison et un vrai service WhatsApp. La valeur initiale viendra davantage de la qualité des données, de la confiance et de l'exécution logistique que d'une longue liste de fonctionnalités.

Une fois les commandes traitées sans erreur et les stocks fiables, ajouter dans cet ordre : retour en stock, packs/routines, avis vérifiés, fidélité, paiement en ligne, arabe, puis personnalisation avancée.
