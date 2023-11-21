<?php
// I, Sujan Rokad, 000882948, certify that this material is my original work.
// No other person's work has been used without suitable acknowledgement and I
// have note made my work available to anyone else.

/** 
 * Nim Game
 *
 * This script implements a simple Nim game with player and computer moves.
 * 
 * @author Sujan Rokad
 * @version 202335.00
 * @package COMP 10260 Assignment 3
*/

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Function to determine the optimal move for the computer
 *
 * This function calculates the optimal move for the computer based on the remaining stones.
 *
 * @param int $stones The remaining stones
 * @return int The optimal move for the computer
 */
function getComputerMove($stones) {
    // Calculate the remainder when dividing the number of stones by 4
    $remainder = $stones % 4;

    // Calculate the remainder when dividing the number of stones by 4
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

/**
 * Function to play the Nim game
 *
 * This function handles the logic of playing the Nim game, including player and computer moves.
 *
 * @param int $mode The game mode (0 for reset, 1 for play)
 * @param int $difficulty The computer's difficulty level (0 for random, 1 for optimal)
 * @param int $count The initial count of stones
 * @param int|null $playerMove The player's move (null if it's the computer's turn)
 */
function playNim($mode, $difficulty, $count, $playerMove) {
    
    // Start the session
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
        'winner' => ($_SESSION['count'] == 0 && isset($playerMove)) ? $_SESSION['winner'] . "!" : "Game in progress",
    );

    // Return JSON-encoded response
    echo json_encode($response);
}

// Sanitize input parameters
$mode = filter_input(INPUT_GET, 'mode', FILTER_VALIDATE_INT);
$difficulty = filter_input(INPUT_GET, 'difficulty', FILTER_VALIDATE_INT);
$count = filter_input(INPUT_GET, 'count', FILTER_VALIDATE_INT);
$playerMove = filter_input(INPUT_GET, 'player_move', FILTER_VALIDATE_INT);

// Check if input is valid, if not, set default values
$mode = ($mode !== null && $mode !== false) ? $mode : 0;
$difficulty = ($difficulty !== null && $difficulty !== false) ? $difficulty : 0;
$count = ($count !== null && $count !== false) ? $count : 20;
$playerMove = ($playerMove !== null && $playerMove !== false) ? $playerMove : null;

// Play the Nim game
playNim($mode, $difficulty, $count, $playerMove);
?>
