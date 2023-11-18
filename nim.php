<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to determine the optimal move for the computer
function getComputerMove($stones) {
    $remainder = $stones % 4;

    switch ($remainder) {
        case 3:
            return 2;
        case 2:
            return 1;
        case 1:
            // It's impossible to make the remainder 1, so take a random number of stones
            return rand(1, 3);
        case 0:
            return 3;
    }
}

function playNim($mode, $difficulty, $count, $playerMove) {
    session_start();

    if ($mode == 0) {
        // Reset the game
        $_SESSION['count'] = $count;
        $_SESSION['player_turn'] = true;
        $_SESSION['winner'] = "undetermined";

        // Prepare the response for the reset
        $response = array(
            'move' => 0,
            'stones' => $_SESSION['count'],
            'player' => "Computer",
            'winner' => "Game is started",
        );

        // Return JSON-encoded response
        echo json_encode($response);
        return;
    } elseif ($_SESSION['count'] > 0) {
        // Game is being played only if count is greater than 0
        if ($_SESSION['player_turn']) {
            // Player's turn
            $_SESSION['count'] -= $playerMove;
            $player = "Player";
            $move = $playerMove;
        } else {
            // Computer's turn
            if ($difficulty == 1) {
                // Optimal play
                $move = getComputerMove($_SESSION['count']);
            } else {
                // Random guess
                $move = rand(1, 3);
            }

            $_SESSION['count'] -= $move;
            $player = "Computer";
        }

        // Switch turns
        $_SESSION['player_turn'] = !$_SESSION['player_turn'];

        // Ensure count is not negative
        $_SESSION['count'] = max(0, $_SESSION['count']);
    }

    // Check for a winner only if at least one move has been made and the count is zero
    if ($_SESSION['count'] == 0 && isset($playerMove)) {
        $_SESSION['winner'] = $player === "Player" ? "Computer" : "Player";
    }


    // Prepare the response
    $response = array(
        'move' => $move,
        'stones' => $_SESSION['count'],
        'player' => $player,
        'winner' => ($_SESSION['count'] == 0 && isset($playerMove)) ? $_SESSION['winner'] . " wins!" : "Game in progress",
    );

    // Return JSON-encoded response
    echo json_encode($response);
}

// Get input parameters
$mode = isset($_GET['mode']) ? intval($_GET['mode']) : 0;
$difficulty = isset($_GET['difficulty']) ? intval($_GET['difficulty']) : 0;
$count = isset($_GET['count']) ? intval($_GET['count']) : 20;
$playerMove = isset($_GET['player_move']) ? intval($_GET['player_move']) : null;

// Play the Nim game
playNim($mode, $difficulty, $count, $playerMove);
?>
