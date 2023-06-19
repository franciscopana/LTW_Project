<?php
    require_once 'components/navbar/navbar.php';
    require_once 'database/database.php';
    require_once 'utils/action_edit_profile.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/remixicon.css">
    <link rel="stylesheet" href="css/profile_page.css">
    <script src="js/profile_page.js" defer></script>
    <script src="js/password.js"></script>
</head>
<body>

<?php
    $db = get_database();

    global $ifHtml;
    $session = is_session_valid($db);
    $client  = null;

    $passwordFailed = null;

if ($session !== null) {
    $client = get_user($session->username, $db);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === "editProfile") {
        $displayName = $_POST['displayName'];
        $email       = $_POST['email'];
        $image       = $_FILES['fileInput'];

        if (edit_profile($client, $email, $displayName, $image, $db) === true) {
            $client = get_user($client->username, $db);
        }
    }

    if ($action === "changePassword") {
        $oldPassword     = $_POST['oldPassword'];
        $newPassword     = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        if (edit_password($client, $oldPassword, $newPassword, $confirmPassword, $db) === true) {
            $client         = get_user($client->username, $db);
            $passwordFailed = false;
        } else {
            $passwordFailed = true;
        }
    }
}

echo navbar($db);


?>
<main>
    <h1>Your Profile</h1>
    <div class="profile-container">
        <div class="image-container">
            <?php if ($client->image !== null) : ?>
                <img src="<?php echo htmlspecialchars($client->image)?>" alt="Client Image" id = "profilePicture">
            <?php else : ?>
                <img src="assets/images/default_user.png" alt="Default User Image" id = "profilePicture">
            <?php endif; ?>
        </div>
        <form action="profile" method="post" enctype="multipart/form-data" onsubmit="return handleSaveClick()">
            <label for="username">Username
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($client->username)?>" autocomplete="off" pattern="^[a-zA-Z0-9_\-\.]{3,20}$" disabled>
            </label>

            <label for="displayName">Name
                <input type="text" name="displayName" id="displayName" value="<?php echo htmlspecialchars($client->displayName)?>" autocomplete="off" required readonly>
            </label>

            <label for="email">Email
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($client->email)?>" required readonly>
            </label>

            <input type="button" id="editButton" value="Edit">
            <div id = "editButtons">
                <input type="button" id="cancelButton" value="Cancel" style="display: none;" onclick = "handleCancelClick()">
                <input type="submit" id="saveButton" value="Save" style="display: none;">
            </div>

            <input type="hidden" name="action" value="editProfile">
            <input type="file" name="fileInput" id="fileInput" style="display: none;" accept="image/*" onchange="handleFileSelect(event)">
        </form>

        <form action = "profile" method = "post" onsubmit>
            <label for="oldPassword">Your current password
                <div class="password-container">
                    <input type="password" name="oldPassword" id="oldPassword" required>
                    <i class="ri-eye-line password-toggle-icon" onclick="togglePasswordVisibility('oldPassword')"></i>
                </div>
            </label>

            <label for="newPassword">New password
                <div class="password-container">
                    <input type="password" name="newPassword" id="newPassword" required>
                    <i class="ri-eye-line password-toggle-icon" onclick="togglePasswordVisibility('newPassword')"></i>
                </div>
            </label>

            <label for="confirmPassword">Confirm new password
                <div class="password-container">
                    <input type="password" name="confirmPassword" id="confirmPassword" required>
                    <i class="ri-eye-line password-toggle-icon" onclick="togglePasswordVisibility('confirmPassword')"></i>
                </div>
            </label>

            <?php if ($passwordFailed === true) : ?>
                <p class="error">Wrong password</p>
            <?php elseif ($passwordFailed === false) : ?>
                <p class="success">Password changed successfully</p>
            <?php endif; ?>

            <input type="hidden" name="action" value="changePassword">
            <input type="submit" value="Change Password" onclick="passwordsMatch('newPassword', 'confirmPassword')">
        </form>
    </div>
</main>

</main>
</body>
</html>
