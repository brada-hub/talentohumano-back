import csv
import json
import requests
import os

# URLs
COUNTRIES_URL = "https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/csv/countries.csv"
STATES_URL = "https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/csv/states.csv"
CITIES_URL = "https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/csv/cities.csv"

# Target Countries
TARGET_ISO2 = ["AR", "BO", "BR", "CL"]
GENTILICIOS = {
    "AR": "Argentina",
    "BO": "Boliviana",
    "BR": "Brasileña",
    "CL": "Chilena"
}

BO_CODES = {
    "La Paz": "LP",
    "Cochabamba": "CB",
    "Santa Cruz": "SC",
    "Beni": "BE",
    "Pando": "PA",
    "Oruro": "OR",
    "Potosí": "PT",
    "Chuquisaca": "CH",
    "Tarija": "TJ",
    "Potosi": "PT"
}

def get_csv_data(url):
    print(f"Downloading {url}...")
    response = requests.get(url)
    response.encoding = 'utf-8'
    return csv.DictReader(response.text.splitlines())

def main():
    # 1. Countries
    countries_data = []
    country_id_map = {}
    for row in get_csv_data(COUNTRIES_URL):
        if row['iso2'] in TARGET_ISO2:
            country = {
                "id": int(row['id']),
                "nombre": row['name'],
                "iso2": row['iso2'],
                "iso3": row['iso3'],
                "prefijo": row['phonecode'],
                "gentilicio": GENTILICIOS[row['iso2']]
            }
            countries_data.append(country)
            country_id_map[row['id']] = country

    # 2. States
    states_data = []
    state_id_map = {}
    for row in get_csv_data(STATES_URL):
        if row['country_id'] in country_id_map:
            country = country_id_map[row['country_id']]
            
            codigo = "EXT"
            if country['iso2'] == "BO":
                name = row['name']
                codigo = BO_CODES.get(name, "EXT")
                if name == "Potosi": codigo = "PT"
                if name == "Chuquisaca": codigo = "CH"

            state = {
                "id": int(row['id']),
                "nombre": row['name'],
                "pais_id": int(row['country_id']),
                "codigo_expedido": codigo
            }
            states_data.append(state)
            state_id_map[row['id']] = state

    # 3. Cities
    cities_data = []
    seen_names = set() # (state_id, name) to handle simple duplicates
    for row in get_csv_data(CITIES_URL):
        if row['state_id'] in state_id_map:
            name = row['name']
            state_id = int(row['state_id'])
            
            # Limpieza: En Bolivia y otros, dr5hn incluye "Provincia X" y "X"
            if name.startswith("Provincia ") or name.startswith("Provincia de"):
                continue
            
            # Evitar duplicados exactos en el mismo estado
            key = (state_id, name)
            if key in seen_names:
                continue
            seen_names.add(key)

            city = {
                "id": int(row['id']),
                "nombre": name,
                "departamento_id": state_id
            }
            cities_data.append(city)

    # 4. Custom Cities (manual additions)
    # Cochabamba ID: 3381 en dr5hn
    CUSTOM_CITIES = [
        {"id": 999999, "nombre": "Colcapirhua", "departamento_id": 3381}
    ]
    for custom in CUSTOM_CITIES:
        cities_data.append(custom)

    # 5. Final Structure
    result = {
        "paises": countries_data,
        "departamentos": states_data,
        "ciudades": cities_data
    }

    output_path = "database/data/geo_data.json"
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print(f"Done! Saved {len(countries_data)} countries, {len(states_data)} states, and {len(cities_data)} cities.")

if __name__ == "__main__":
    main()
