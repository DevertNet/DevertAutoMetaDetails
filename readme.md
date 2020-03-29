Tested with:
6.1

Checklist:
- Aneltiung
- - Cache
- - Beispiele
- - Übersetzung von phrasen; Hinweis bei mix
- - Max Länge aus SW Config
- Prod

Allgemein
- Shop Name {{ context.salesChannel.name }}
- System Config {{ shopware.config.core.basicInformation.shopName }}
- Probleme mit Sonderzeichen |raw
- Snippet {{ "detail.addProduct"|trans|sw_sanitize }}
- Twig Funktionen {{ name|slice(0, 10) }} 

Produkte
- Produkt Object: {{ page.product }}
- Kontext Object: {{ context }}
- Name {{ name }}
- Beschreibung {{ description }}
- Artikelnummer {{ page.product.productnumber }}
- EAN {{ page.product.ean }}
- Hersteller {{ page.product.manufacturer.translated.name }}
- Bestand {{ page.product.stock }} {{ page.product.availableStock }}
- Bewertung als Zahl (1-5) {{ page.product.ratingAverage }}
- Attribut {{ page.product.customFields.custom_shoes_quos_earum_non }}
- Optionen {% for option in page.selectedOptions %}{{ option.group.translated.name|e }} {{ option.translated.name|e }}{% endfor %}
- Preis {{ price }}

Kategorien
- Kategorie Object {{ category }}
- Kontext Object: {{ context }}
- Name {{ name }}
- Beschreibung {{ description }}