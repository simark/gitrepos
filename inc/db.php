<?php

require_once("config.php");
require_once("database.class.php");

require_once("inc/User.php");
require_once("inc/Repository.php");
require_once("inc/Permission.php");

function db_connect() {
	return new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
}

/**
 * Closes the connection to the database.
 */
function db_close($db) {
	$db->fermer();
}

/**
 * Gets all the permissions of the system.
 * 
 * @return An array of permissions.
 */
function db_get_all_perms($db) {
	$db->query("SELECT * FROM perms", $db);
	
	return $db->fetchAll();
}

/**
 * Gets the permissions a user has on other users' repositories.
 * 
 * @param username The name of the user.
 * @return An array of permissions.
 */
function db_get_user_perms($db, $unsafe_openid) {
	$openid = $db->escape($unsafe_openid);

  $db->query(
    "SELECT r.name as repo, p.name as level, r.description
       FROM Users as u, Repos as r, Perms as p, UserRepoPerms as urp
       WHERE u.openid = '$openid' AND u.id = urp.user AND urp.perm = p.id AND urp.repo = r.id
       ORDER BY r.name DESC");

  $perms = $db->fetchAll();
	return $perms;
}

/**
 * Gets the permissions a user has on other users' repositories.
 * 
 * @param username The name of the user.
 * @return An array of permissions.
 */
function db_get_all_repos($db) {
	$db->query("SELECT* FROM repos ORDER BY repos.name DESC");
	
	$repos = array();
	
	$repo = $db->fetch();
	while ($repo) {
		$repos[$repo['name']] = $repo;
		$repos[$repo['name']]['perms'] = array();
		
		$repo = $db->fetch();
	}
	
	$perms = db_get_all_perms($db);
	
	foreach ($perms as $perm) {
		$repos[$perm['repo']]['perms'][] = $perm;
	}
	
	return $repos;
}

function db_get_names($db, $unsafe_openid) {
  $openid = $db->escape($unsafe_openid);

  $db->query("SELECT u.username, u.name FROM Users as u WHERE u.openid = '$openid'");
  return $db->fetch();
}

/**
 * Gets the repositories owned by a user.
 * 
 * @param userid OpenID of the user.
 * @return A list of repositories.
 */
function db_get_user_repos($db, $unsafe_openid) {
	$openid = $db->escape($unsafe_openid);
	
	$db->query(
    "SELECT r.name, r.description
       FROM Users as u, Repos as r, Perms as p, UserRepoPerms as urp
       WHERE u.openid = '$openid' AND u.id = urp.user AND urp.perm = p.id AND p.name = 'RW+' AND urp.repo = r.id
       ORDER BY r.name DESC");
	
	$repos = array();
	
	$repo = $db->fetch();
	while ($repo) {
		$repos[$repo['name']] = $repo;
		$repos[$repo['name']]['perms'] = array();
		
		$repo = $db->fetch();
	}

	// Get permissions related to this users' repo
  $db->query(
    "SELECT u.username, r.name as repo, p.name as level
       FROM Users as u,Perms as p, UserRepoPerms as urp,
        ((SELECT r.id, r.name, r.description
            FROM Users as u, Repos as r, Perms as p, UserRepoPerms as urp
             WHERE u.openid = '$openid' AND u.id = urp.user AND urp.perm = p.id AND p.name = 'RW+' AND urp.repo = r.id
        )) as r
       WHERE r.id = urp.repo && u.id = urp.user && p.id = urp.perm
       ORDER BY r.name DESC");
	            
	$perms = $db->fetchAll();

  //echo '<pre>';
  //print_r($perms);
  //echo '</pre>';
	
	foreach ($perms as $perm) {
		$repos[$perm['repo']]['perms'][] = $perm;
	}

	return $repos;
}

/**
 * Gets the permissions related to a repository.
 * 
 * @param reponame The name of the repository.
 * @return An array of permissions.
 */
function db_get_repo_perms($db, $reponame) {
	$reponame_safe = $db->escape($reponame);
	
	$db->query("SELECT * FROM perms WHERE perms.repo = '$reponame_safe'");
	
	return $db->fetchAll();
}

/**
 * Gets a repository.
 * 
 * @param reponame The name of the repository.
 * @return The repository, or false if there is no repository with that
 *     name.
 */
function db_get_repo_by_name($db, $unsafe_name) {
  $name = $db->escape($unsafe_name);
	
	$db->query("SELECT * FROM Repos as r WHERE r.name = '$name'");

	return $db->fetch();
}

function db_get_user_by_username($db, $unsafe_username) {
  $username = $db->escape($unsafe_username);

  $db->query("SELECT * FROM Users as u WHERE u.username = '$username'");
  return $db->fetch();
}
/**
 * Gets a repository specifying its name and the name of the owner.
 * 
 * @param unsafe_repo Repository's name.
 * @param unsafe_openid User's OpenID
 * @return The repository, or false if there is no repository with that
 *     name.
 */
function db_get_repo_user($db, $unsafe_repo, $unsafe_openid) {
	$repo = $db->escape($unsafe_repo);
	$openid = $db->escape($unsafe_openid);

  $db->query(
    "SELECT u.id as uid, u.name, p.id as pid, p.name as level, u.username, r.id
       FROM Users as u,Perms as p, UserRepoPerms as urp,
        ((SELECT r.id
            FROM Users as u, Repos as r, UserRepoPerms as urp
             WHERE u.openid = '$openid' AND u.id = urp.user AND r.name = '$repo' AND urp.repo = r.id
        )) as r
       WHERE r.id = urp.repo && u.id = urp.user && p.id = urp.perm");

  $repo = $db->fetchAll();
	if (!$repo) {
		return false;
	}
	
	return $repo;
}

/**
 * Gets a user record with the email.
 * 
 * @param email The email of the user.
 * @return The user info if the email exists, false otherwise.
 */
function db_get_user_by_email($db, $email) {
	$email_safe = $db->escape($email);
	
	$db->query("SELECT * FROM users WHERE users.email = '$email_safe'");
	
	return $db->fetch();
}

/**
 * Adds a repository.
 * 
 * @param reponame Name of the repository to add.
 * @param owner Name of the repository owner.
 * @return E_SUCCESS if the repository was successfully added.
 *         E_REPO_EXISTS if a repository already exists with that name.
 *         E_USER_NO_EXISTS if the username specified for the owner does
 *             not exist.
 */
function db_add_repo($db, $name, $description, User $user) {
  $result = Repository::Create($db, $name, $description);
  if ($result != E_SUCCESS)  return $result;

  $repo = Repository::ByName($db, $name);
  if ($repo == null)  return E_WEIRD_SHIT;

  $result = Permission::SetPerm($db, Permission::Admin($db), $user, $repo);
  Repository::SetAdmin($db, $user, $repo);
  Repository::SetOwner($db, $user, $repo);

	return $result;
}

/**
 * Gets the permission of a user on a certain repository.
 * 
 * @param reponame Name of the repository.
 * @param username Name of the user.
 * @return The permission if it exists, false otherwise.
 */
function db_get_perm($db, $reponame, $username) {
	$reponame_safe = $db->escape($reponame);
	$username_safe = $db->escape($username);
	
	$db->query("SELECT * FROM perms WHERE perms.repo = '$reponame_safe' AND perms.user = '$username_safe'");
	
	return $db->fetch();
}


function db_is_user_admin_on_repo($db, $unsafe_repoID, $unsafe_userID) {
  $repoID = $db->escape($unsafe_repoID);
  $userID = $db->escape($unsafe_userID);

  $db->query("SELECT u.id FROM Users as u, Perms as p, UserRepoPerms as urp
       WHERE u.id = '$userID' AND u.id = urp.user AND urp.perm = p.id AND p.name = 'RW+' AND urp.repo = '$repoID'");

  return $db->fetch();
}

/**
 * Creates or updates the permission of a user on a repository.
 * 
 * @param reponame Name of the repository.
 * @param username Name of the user. It should not be the owner of the
 *     repository.
 * @param level Permission level, one of 'R', 'RW', 'RW+'.
 * @return
 *     E_SUCCESS if the permission was set successfully.
 *     E_INVALID_LEVEL if an invalid permission level was given.
 *     E_REPO_NO_EXISTS if no repository exists with that name.
 *     E_USER_NO_EXISTS if no user exists with that name.
 *     E_INVALID_USER if the given user is owner of the repository.
 */
function db_set_perm($db, $reponame, $username, $level) {
	$reponame_safe = $db->escape($reponame);
	$username_safe = $db->escape($username);
	$level_safe = $db->escape($level);
	
	global $valid_perm_levels;
	
	/* Check if perm level is valid */
	if (!in_array($level, $valid_perm_levels)) {
		return E_INVALID_LEVEL;
	}
	
	/* Check if repo exists */
	$repo = db_get_repo($db, $reponame);
	if (!$repo) {
		return E_REPO_NO_EXISTS;
	}
	
	/* Check if user is not owner of the repo */
	if ($repo['owner'] == $username) {
		return E_INVALID_USER;
	}
	
	/* Check if user exists */
	if (!db_get_user($db, $username)) {
		return E_USER_NO_EXISTS;
	}

	$db->query("INSERT INTO perms (user, repo, level) VALUES ('$username_safe',
		'$reponame_safe', '$level_safe') ON DUPLICATE KEY UPDATE
		level='$level_safe'");
	
	return E_SUCCESS;
}

/**
 * Removes the permissions of a user on a repository.
 * 
 * @param reponame Name of the repository.
 * @param username Name of the user.
 * @return True if the entry was deleted, false if there was no such
 *     combination of repo / user.
 */
function db_del_perm($db, $reponame, $username) {
	$reponame_safe = $db->escape($reponame);
	$username_safe = $db->escape($username);
	
	/* Check if this combination exists */
	if (db_get_perm($db, $reponame, $username) == false) {
		return false;
	}
	
	$db->query("DELETE FROM perms WHERE perms.user = '$username_safe' AND perms.repo = '$reponame_safe'");
	
	return true;
}

/**
 * Adds an user.
 * 
 * @param username Name of the user to add.
 * @return E_SUCCESS if the user was successfully added.
 *         E_USER_EXISTS if a user already exists with that name.
 *         E_EMAIL_EXISTS if the email address is already used.
 */
function db_add_user($db, $username, $email, $password) {
	$username_safe = $db->escape($username);
	$email_safe = $db->escape($email);
	$password_safe = $db->escape($password);
	
	/* Check if user already exists */
	if (db_get_user($db, $username) != false) {
		return E_USER_EXISTS;
	}
	
	/* Check if email is already in use */
	if (db_get_user_by_email($db, $email) != false) {
		return E_EMAIL_EXISTS;
	}
	
	/* Insert in DB */
	$db->query("INSERT INTO users (name, email, password) VALUES (
		'$username_safe', '$email_safe', '$password_safe');");

	return E_SUCCESS;
}

/**
 * Sets a user password.
 *
 * @param username The username for which to set the password.
 * @param password The password already hashed.
 * @return E_SUCCESS On success.
 *         E_USER_NO_EXISTS If the username given does not exist.
 */
function db_set_password($db, $username, $password) {
	$username_safe = $db->escape($username);
	$password_safe = $db->escape($password);

	if (!db_get_user($db, $username)) {
		return E_USER_NO_EXISTS;
	}

	$db->query("UPDATE users SET password = '$password_safe' WHERE name = '$username_safe';");

	return E_SUCCESS;
}

?>
