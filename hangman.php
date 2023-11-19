<?php
session_start();

function resetGame() {
    $wordlist = file("wordlist.txt", FILE_IGNORE_NEW_LINES);
    $secret = trim(strtoupper($wordlist[array_rand($wordlist)]));
    $_SESSION['secret'] = str_split($secret);
    $_SESSION['guesses'] = [];
    $_SESSION['alphabet'] = range('A', 'Z');
    $_SESSION['strikes'] = 0;
    $_SESSION['status'] = "New game has started";
}

function makeGuess($letter) {
    $letter = strtoupper($letter);

    // Check if the game has already ended
    if ($_SESSION['status'] === "Congratulations! You won!" || $_SESSION['status'] === "Sorry, you lost. The word was " . implode('', $_SESSION['secret']) . ".") {
        return;
    }

    if (!in_array($letter, $_SESSION['guesses'])) {
        $_SESSION['guesses'][] = $letter;

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
            $_SESSION['status'] = "Sorry, you lost. The word was " . implode('', $_SESSION['secret']) . ".";
        }
    } else {
        $_SESSION['status'] = "You already guessed that letter. Try another one.";
    }
}


// Check for AJAX GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['letter'])) {
        makeGuess($_GET['letter']);
    } elseif (isset($_GET['mode']) && $_GET['mode'] == 'reset') {
        resetGame();
    }
}

// Prepare JSON response
$response = [
    'guesses' => implode(', ', $_SESSION['guesses']),
    'alphabet' => implode(', ', $_SESSION['alphabet']),
    'secret' => implode(' ', array_map(function ($letter) {
        return in_array($letter, $_SESSION['guesses']) ? $letter : '_';
    }, $_SESSION['secret'])),
    'strikes' => $_SESSION['strikes'],
    'status' => $_SESSION['status'],
];


echo json_encode($response);
?>
