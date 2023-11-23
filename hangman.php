<?php

// I, Sujan Rokad, 000882948, certify that this material is my original work.
// No other person's work has been used without suitable acknowledgement and I
// have note made my work available to anyone else.

/** 
 * @author Sujan Rokad
 * @version 202335.00
 * @package COMP 10260 Assignment 3
*/

// Start the session
session_start();

/**
 * Function to reset the game
 *
 * This function reads the word list from a file, selects a random word, and initializes/reset session variables.
 */
function resetGame() {

    // Read the word list from the file
    $wordlist = file("wordlist.txt",
    FILE_IGNORE_NEW_LINES);

    // Select a random word from the array
    $secret = trim(strtoupper($wordlist[array_rand($wordlist)]));

    // Store the selected word as an array of letters in the session
    $_SESSION['secret'] = str_split($secret);

    // Initialize or reset other session variables
    $_SESSION['guesses'] = [];
    $_SESSION['alphabet'] = range('A', 'Z');
    $_SESSION['strikes'] = 0;
    $_SESSION['status'] = "New game has started";
}

/**
 * Function to process a user's guess
 *
 * This function processes the user's guess, updates session variables, and determines the game status.
 *
 * @param string $letter The guessed letter
 */
function makeGuess($letter) {
    // Convert the guessed letter to uppercase
    $letter = strtoupper($letter);

    // Check if the game has already ended
    if ($_SESSION['status'] === "Congratulations! You won!" || $_SESSION['status'] === "Sorry, you lost. The word was " . implode('', $_SESSION['secret']) . ".") {
        return;
    }

    // Check if the letter has not been guessed before
    if (!in_array($letter, $_SESSION['guesses'])) {
        // Add the guessed letter to the list of guesses
        $_SESSION['guesses'][] = $letter;

        // Check if the guessed letter is the secret word
        if (in_array($letter, $_SESSION['secret'])) {
            // Correct guess
            $_SESSION['status'] = "Good guess!";
        } else {
            // Incorrect guess
            $_SESSION['strikes']++;
            $_SESSION['status'] = "Wrong guess!";
        }

        // Remove guessed letter from the alphabet
        $_SESSION['alphabet'] = array_values(array_diff($_SESSION['alphabet'], [$letter]));

        // Check if the user has won
        if (count(array_diff($_SESSION['secret'], $_SESSION['guesses'])) === 0) {
            $_SESSION['status'] = "Congratulations! You won!";
        }

        // Check if the user has lost
        if ($_SESSION['strikes'] >= 7) {
            $_SESSION['status'] = "Sorry, you have lost";
        }
    } else {
        $_SESSION['status'] = "You already guessed that letter. Try another one.";
    }
}

// Check for AJAX GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if a letter is provided in the GET parameters
    if (isset($_GET['letter'])) {
        // Process the user's guess
        makeGuess($_GET['letter']);
        // Check if the "reset" mode is requested
    } elseif (isset($_GET['mode']) && $_GET['mode'] == 'reset') {
        // Reset the game
        resetGame();
    }
}

// Prepare JSON response
$response = [
    'guesses' => implode(', ', $_SESSION['guesses']),
    'alphabet' => implode(', ', $_SESSION['alphabet']),
    'secret' => $_SESSION['strikes'] >= 7 ? implode('', $_SESSION['secret']) : implode(' ', array_map(function ($letter) {
        return in_array($letter, $_SESSION['guesses']) ? $letter : '_';
    }, $_SESSION['secret'])),
    'strikes' => $_SESSION['strikes'],
    'status' => $_SESSION['status'],
];

// Output the JSON response
echo json_encode($response);
?>