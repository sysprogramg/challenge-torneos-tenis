<?php
    require_once __DIR__ . '/../../vendor/autoload.php';

    class SheetManager
    {
        private function __construct() {}

        const COLUMNS_LETTER = array (
            'tourney_year' => 'A',
            'tourney_name' => 'C',
            'tourney_slug' => 'E',
            'singles_winner_name' => 'P',
            'singles_winner_player_slug' => 'R'
        );
        
        private static function getClient()
        {
            $client = new Google_Client();
            $client->setApplicationName('Google Sheets API');
            $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
            $client->setAuthConfig('../../credentials.json');
            $client->setAccessType('offline');
        
            return $client;
        }
        
        private static function getRanges()
        {
            $ranges = array();
            foreach(self::COLUMNS_LETTER as $column)
                array_push($ranges, sprintf("%s2:%s", $column, $column));
            return $ranges;
        }
        
        private static function getIndiceColumnaByNombre($nombre)
        {
            $key = array_search($nombre, array_keys(self::COLUMNS_LETTER));
            return ($key === false ? -1 : $key);
        }

        public static function GetTopWinnersByTourneySlug($tourney_slug, $top, $sheet_id)
        {
            try {
                $client = self::getClient();
                $service = new Google_Service_Sheets($client);
                $params = array(
                    'ranges' => self::getRanges(),
                    'majorDimension' => 'COLUMNS'
                );
                $response = $service->spreadsheets_values->batchGet($sheet_id, $params);
                $values = $response->getValueRanges();
                if (empty($values)) return array();
                $ganadores = array();
                foreach ($values[self::getIndiceColumnaByNombre('tourney_slug')]['values'][0] as $key => $value) {
                    if ($value != $tourney_slug) continue;
                    $singles_winner_player_slug = $values[self::getIndiceColumnaByNombre('singles_winner_player_slug')]['values'][0][$key];
                    $singles_winner_name = $values[self::getIndiceColumnaByNombre('singles_winner_name')]['values'][0][$key];
                    $tourney_year = $values[self::getIndiceColumnaByNombre('tourney_year')]['values'][0][$key];
                    if (array_key_exists($singles_winner_player_slug, $ganadores)) {
                        $ganadores[$singles_winner_player_slug]['titulos']++;
                        $ganadores[$singles_winner_player_slug]['ano'][] = $tourney_year;
                    } else {
                        $ganadores[$singles_winner_player_slug]['nombre'] = $singles_winner_name;
                        $ganadores[$singles_winner_player_slug]['titulos'] = 1;
                        $ganadores[$singles_winner_player_slug]['ano'][] = $tourney_year;
                        
                    }
                }
                array_multisort(array_column($ganadores, 'titulos'),SORT_DESC, SORT_NUMERIC,
                    array_column($ganadores, 'ano'),
                    $ganadores);

                return array_slice($ganadores, 0, $top);
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        public static function GetTilesTourneyByPlayerSlug($tourney_slug, $player_slug, $sheet_id)
        {
            try {
                $client = self::getClient();
                $service = new Google_Service_Sheets($client);
                $params = array(
                    'ranges' => self::getRanges(),
                    'majorDimension' => 'COLUMNS'
                );
                $response = $service->spreadsheets_values->batchGet($sheet_id, $params);
                $values = $response->getValueRanges();
                if (empty($values)) return array();
                $titulos = array();
                foreach ($values[self::getIndiceColumnaByNombre('tourney_slug')]['values'][0] as $key => $value) {
                    $singles_winner_player_slug = $values[self::getIndiceColumnaByNombre('singles_winner_player_slug')]['values'][0][$key];
                    if ($value == $tourney_slug && $singles_winner_player_slug == $player_slug) {
                        $tourney_year = $values[self::getIndiceColumnaByNombre('tourney_year')]['values'][0][$key];
                        $titulos[] = $tourney_year;
                    }
                }
                rsort($titulos, SORT_NUMERIC);

                return $titulos;
            } catch (\Throwable $th) {
                throw $th;
            } 
        }
    }
?>