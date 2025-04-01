<?php

namespace App\Services;

class MapDataService
{
    public function mapData()
    {
        return [
            [
                "name" => "Observatório de Favelas",
                "type" => "Espaços de cultura ou comunitários",
                "lat" => -22.856098,
                "lng" => -43.247092,
                "accessible" => true,
                "partial_accessible" => false,
                "accessibility_features" => ["rampas", "piso tátil", "banheiro acessível"],
                "surroundings_accessible" => false,
                "surroundings_issues" => "Calçadas irregulares e bloqueios"
            ],
            [
                "name" => "Supermercado Vianense - Passarela 9",
                "type" => "Comércio ou serviço",
                "lat" => -22.856778,
                "lng" => -43.247539,
                "accessible" => true,
                "partial_accessible" => true,
                "accessibility_features" => ["rampas", "escadas adaptadas"],
                "surroundings_accessible" => false,
                "surroundings_issues" => "O supermercado é acessível mas o banheiro não é acessível.",
                "url" => asset('images/supermercado-vianense.png')
            ],
            [
                "name" => "Ritma - Redes da Maré",
                "type" => "Espaços de cultura ou comunitários",
                "lat" => -22.856118,
                "lng" => -43.24717,
                "accessible" => true,
                "partial_accessible" => false,
                "accessibility_features" => ["rampas", "placas em Braille", "ponto de ônibus acessível"],
                "surroundings_accessible" => false,
                "surroundings_issues" => "Há faixa sinalizando a entrada para cadeirantes PcD"
            ],
            [
                "name" => "Buraco - Flávia Farnese / 29 de Julho",
                "type" => "Espaço comunitário, calçada",
                "lat" => -22.858070,
                "lng" => -43.246020,
                "accessible" => false,
                "partial_accessible" => false,
                "accessibility_features" => ['totalmente inacessível', 'calçada com buraco', 'obstáculos'],
                "surroundings_accessible" => false,
                "surroundings_issues" => "Buraco na calçada não é possível transitar",
                "url" => asset('images/buraco.png')
            ],
            [
                "name" => "R. Aymore, 86 - Maré",
                "type" => "Espaço comunitário, calçada",
                "lat" => -22.854679,
                "lng" => -43.244958,
                "accessible" => false,
                "partial_accessible" => false,
                "accessibility_features" => ['inacessível', 'calçada irregular', 'obstáculos'],
                "surroundings_accessible" => false,
                "surroundings_issues" => "Calçada irregular e bloqueios, não acessível para cadeirantes",
                "url" => asset('images/aymore.png')
            ],
            [
                "name" => "Garota da Teixeira",
                "type" => "Comércio ou serviço",
                "lat" => -22.855835,
                "lng" => -43.244229,
                "accessible" => false,
                "partial_accessible" => false,
                "accessibility_features" => ["falta de rampa de acesso", "passagens estreitas ou irregulares", "escadas sem corrimão"],
                "surroundings_accessible" => false,
                "surroundings_issues" => "Calçadas irregulares e barreiras nos trajetos, não tem corrimão.",
            ],
            [
                "name" => "Rampa da Passarela 9",
                "type" => "Rua, calçada ou passagem",
                "lat" => -22.85656,
                "lng" => -43.247389,
                "accessible" => false,
                "partial_accessible" => false,
                "accessibility_features" => ["falta de rampa de acesso", "falta de piso tátil"],
                "surroundings_accessible" => true,
                "surroundings_issues" => "Falta de rampa de acesso o lugar é inacessível para cadeirantes",
            ],
        ];
    }

    public static function getMapData()
    {
        return (new self)->mapData();
    }
}