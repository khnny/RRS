<?php
// Choose a strong password!
$plain_password = 'test123'; // Replace with your desired password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Username: admin<br>";
echo "Role: admin<br>";
echo "Password Hash (copy this value): <br>";
echo '<textarea rows="3" cols="80" readonly>' . htmlspecialchars($hashed_password) . '</textarea>';

// You can add more details like full_name and email if desired
?>