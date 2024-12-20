<?php
include('database.php');


// Load the HTML file
$html = file_get_contents('5index.html');

// Generate the dynamic content for the table
$challengeRows = '';
foreach ($challenges as $challenge) {
    $challengeRows .= "
        <tr>
            <td>{$challenge['name']}</td>
            <td>{$challenge['description']}</td>
            <td>{$challenge['status']}</td>
            <td>
                <a href='assign_task.php?id={$challenge['id']}'>Assign Task</a> | 
                <a href='track_progress.php?id={$challenge['id']}'>Track Progress</a>
            </td>
        </tr>";
}

// Replace the placeholder in the HTML file
$html = str_replace('<!-- Dynamic content goes here -->', $challengeRows, $html);

// Output the final page
echo $html;
?>
