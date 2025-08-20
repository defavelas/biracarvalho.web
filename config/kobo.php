<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Kobo API URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the URL for the Kobo API.
    |
    */
    'api_url' => env('KOBO_API_URL', 'https://kf.kobotoolbox.org'),

    /*
    |--------------------------------------------------------------------------
    | Kobo API Token (Credentials)
    |--------------------------------------------------------------------------
    |
    | Here you may specify the token for the Kobo API, used to authenticate
    | requests as Bearer Token.
    |
    */
    'api_token' => env('KOBO_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Kobo API Form IDs
    |--------------------------------------------------------------------------
    |
    | Here you may specify the form IDs for the Kobo API.
    |
    */
    'accessible_form_id' => 'ars3r7q7nftPBH857jBfp8',

    /*
    |--------------------------------------------------------------------------
    | Kobo API Non Accessible Form ID
    |--------------------------------------------------------------------------
    |
    | Here you may specify the form ID for the non accessible locations
    | collected by the KoboToolbox survey.
    |
    */
    'non_accessible_form_id' => 'acUMxwbvhW25C28vkiZryd',

    /*
    |--------------------------------------------------------------------------
    | Kobo API Values Mapping
    |--------------------------------------------------------------------------
    |
    | Here you may specify the values mapping for the Kobo API.
    | This is used to map the values from the Kobo API to the values in the
    | database.
    |
    */
    'accessible' => [
        // Question 1: Na sua perspectiva este lugar é acessível?
        'sim' => 'Sim, é Acessível / Ponto com acessibilidade',
        'n_o' => 'Não, mas tem potencial para acessibilizar',
        
        // Question 3: Que tipo de lugar é esse?
        'espa_os_de_cultura_ou_comunit_rios' => 'Espaços de cultura ou comunitários',
        'escolas__unidades_de_sa_de_ou_outros_ser' => 'Escolas, unidades de saúde ou outros serviços públicos',
        
        // Question 5: Quais elementos de infraestrutura e mobilidade fazem este local acessível?
        'entrada_acess_vel__rampas_bem_constru_da' => 'Entrada acessível (rampas bem construídas, portas largas, etc.)',
        'circula_o_segura__cal_adas_niveladas__se' => 'Circulação segura (calçadas niveladas, sem obstáculos, etc.)',
        'piso_t_til_presente__ajuda_na_orienta_o_' => 'Piso tátil presente (ajuda na orientação de pessoas cegas)',
        'banheiro_acess_vel_dispon_vel__espa_o_ad' => 'Banheiro acessível disponível (espaço adequado, barras de apoio, etc.)',
        
        // Question 5.1: Quais elementos de transporte e deslocamento fazem este local acessível?
        'transporte_p_blico_adaptado__nibus_com_e' => 'Transporte público adaptado (ônibus com elevador ou espaço reservado)',
        'ponto_de__nibus_acess_vel__sinaliza_o__r' => 'Ponto de ônibus acessível (sinalização, rampa, etc.)',
        
        // Question 5.2: Quais elementos de comunicação e interação fazem este local acessível?
        'informa_es_visuais_e_sonoras_claras__avi' => 'Informações visuais e sonoras claras (aviso sonoro em transporte, menus acessíveis, etc.)',
        'placas_e_sinaliza_es_acess_veis__braille' => 'Placas e sinalizações acessíveis (braille, contraste, etc.)',
        
        // Question 5.3: Quais são os outros elementos que fazem este local acessível?
        'ambiente_confort_vel_e_seguro__boa_ilumi' => 'Ambiente confortável e seguro (boa iluminação, espaço para mobilidade)',
        
        // Question 6: O entorno imediato desse local é acessível?
        'sim__o_entorno___acess_vel' => 'Sim, o entorno é acessível',
        'n_o__h__barreiras_de_acessibilidade' => 'Não, há barreiras de acessibilidade',
    ],

    'non_accessible' => [
        // Question 1: Na sua perspectiva este lugar é acessível?
        'sim' => 'Sim, é Acessível / Ponto com acessibilidade',
        'n_o' => 'Não, mas tem potencial para acessibilizar',
        
        // Question 3: Que tipo de lugar é esse?
        'casa_ou_pr_dio_residencial' => 'Casa ou prédio residencial',
        'rua__cal_ada_ou_passagem' => 'Rua, calçada ou passagem',
        'escolas__unidades_de_sa_de_ou_outros_ser' => 'Escolas, unidades de saúde ou outros serviços públicos',
        'com_rcio_ou_servi_o__mercado__farm_cia__' => 'Comércio ou serviço (mercado, farmácia, etc.)',
        
        // Question 5: Quais barreiras de infraestrutura e mobilidade tornam este local não acessível?
        'falta_de_rampa_de_acesso_ou_rampa_muito_' => 'Falta de rampa de acesso ou rampa muito íngreme',
        'passagens_estreitas_ou_irregulares__bura' => 'Passagens estreitas ou irregulares (buracos, degraus, obstáculos)',
        'falta_de_piso_t_til_para_orienta_o_de_pe' => 'Falta de piso tátil para orientação de pessoas cegas',
        'banheiro_sem_acessibilidade__espa_o_pequ' => 'Banheiro sem acessibilidade (espaço pequeno, sem barras de apoio)',
        
        // Question 5.1: Quais barreiras de transporte e deslocamento tornam este local não acessível?
        'transporte_p_blico_sem_adapta_o__nibus_s' => 'Transporte público sem adaptação (ônibus sem elevador ou espaço reservado)',
        'ponto_de__nibus_sem_acessibilidade__sem_' => 'Ponto de ônibus sem acessibilidade (sem rampa, sinalização inadequada)',
        
        // Question 5.2: Quais barreiras de comunicação e interação tornam este local não acessível?
        'falta_de_placas_e_sinaliza_es_acess_veis' => 'Falta de placas e sinalizações acessíveis (sem braille, baixo contraste)',
        'falta_de_informa_es_visuais_ou_sonoras__' => 'Falta de informações visuais ou sonoras (sem avisos sonoros, menus não acessíveis)',
        'atendimento_n_o_inclusivo__sem_libras_ou' => 'Atendimento não inclusivo (sem libras ou outras formas de comunicação)',
        
        // Question 5.3: Quais são os outros elementos que tornam este local não acessível?
        'falta_de_tecnologia_assistiva__ex__caixa' => 'Falta de tecnologia assistiva (ex: caixa eletrônico com áudio)',
        'ambiente_desconfort_vel_e_inseguro__pouc' => 'Ambiente desconfortável e inseguro (pouca iluminação, espaço inadequado)',
        
        // Question 6: O entorno imediato desse local é acessível?
        'sim__o_entorno___acess_vel' => 'Sim, o entorno é acessível',
        'n_o__h__barreiras_de_acessibil' => 'Não, há barreiras de acessibilidade',
    ]
];
