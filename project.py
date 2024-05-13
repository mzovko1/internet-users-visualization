import geopy
import folium
import json

# Učitavanje podataka iz JSON datoteke
with open('timeInternet.json') as f:
    podaci = json.load(f)

# Inicijalizacija geolokatora
geolocator = geopy.geocoders.Nominatim(user_agent="my_geocoder")

# Stvaranje karte svijeta
karta = folium.Map(location=[0, 0], zoom_start=2)

# Dodavanje kružnih markera na kartu za svaku državu s podacima
for podatak in podaci:
    drzava = podatak['Entity']
    broj_korisnika = podatak['Number of Internet users']
    
    # Dobivanje geografskih koordinata na temelju naziva države
    lokacija = geolocator.geocode(drzava)
    if lokacija:
        lat = lokacija.latitude
        lon = lokacija.longitude
        
        # Dodavanje kružnog markera s informacijama o broju korisnika interneta
        folium.CircleMarker(
            location=[lat, lon],
            radius=5,
            popup=f'{drzava}: {broj_korisnika} korisnika interneta',
            fill=True,
            fill_color='blue',  # Ovdje se može postaviti boja prema potrebi
            color='blue',
            fill_opacity=0.7
        ).add_to(karta)

# Spremanje karte kao HTML datoteke
karta.save('vizualizacija_prema_drzavi.html')
