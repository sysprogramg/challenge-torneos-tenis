<?php
    error_reporting(E_ALL ^ E_WARNING); 
    require_once __DIR__ .'./sheet_manager.php';

    if (!isset($_GET['accion'])) 
        die;

    switch ($_GET['accion']) {
        case 'GetTourneyBySlug':
            echo json_encode(GetTourneyBySlug());
            break;
        case 'GetTilesTourneyByPlayerSlug':
            echo json_encode(GetTilesTourneyByPlayerSlug());
            break;
    }

    function GetTourneyBySlug() {
        try {
            return SheetManager::GetTopWinnersByTourneySlug($_GET['tourney_slug'], $_GET['top'], $_GET['sid']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function GetTilesTourneyByPlayerSlug() {
        try {
            return SheetManager::GetTilesTourneyByPlayerSlug($_GET['tourney_slug'], $_GET['player_slug'], $_GET['sid']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
?>