# alfred

### Requirement
* php
* php-zip
* php-sqlite3

1) La liste initiale(553159 mots) a été construite à partir du registre Morphalou-2.0 du CNRTL(Extraction/conversion des données XML originales)
2) Chaque mots ont été recherchés(contre-vérification) dans le wiktionnaire pour acquérir la définition.
3) Après les vérifications croisés, il apparaît que plus de 93% des mots du Morphalou-2.0 sont défini 'tel quel' dans le wiktionnaire.
4) Lorsque le mot entré figure au Morphalou-2.0 mais aucune définition n'a été trouvé a l'heure actuel pour le mot, alors un résultat CSV/TEXTE est retourné.(ex: ôs)
5) Lorsque le mot entré ne figure pas au Morphalou-2.0, alors le mot recherché est aiguillé vers le wiktionnaire pour un traitement supplémentaire.(ex: xyz, le mots dérrivés de d'autres langues, les erreurs)
