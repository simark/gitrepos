<?php
/*
 * Classe Database 
 *
 * Cette classe encapsule la plupart des fonctionnalités proposées par 
 * la base de données. L'avantage est que les appels MySQL sont cachés 
 * derrière les méthodes de ces classes et cela permet de facilement
 * changer de type de base de données.
 *
 * Version originale: Maxime Servonnet 2011-03-23
 * Modification: Simon Marchi 2011-05-19
 * 
 */

class Database
{
	/**
	 * Constructeur
	 * 
	 * @param server Adresse du serveur MySQL.
	 * @param db_name Nom de la base de données.
	 * @param username Nom d'utilisateur pour cette connexion.
	 * @param password Mot de passe pour cette connexion.
	 */
	public function __construct($server, $db_name, $username, $password)
	{
		$this->server_ = $server;
		$this->db_name_ = $db_name;
		$this->username_ = $username;
		$this->password_ = $password;
	}

	/**
	 * Effectue la connexion à la base de données.
	 */
	public function connecter()
	{
		/* On se connecte au serveur SQL. */
		$this->connexion_ = mysql_connect($this->server_, $this->username_, $this->password_);
		
		if (!$this->estConnecte()) {
			$this->error();
		}
		
		$this->choisirBD();
	}
	
	/**
	 * Sélectionne la base de données.
	 * 
	 * @param db_name Nom de la base de données (optionnel)
	 */
	public function choisirBD($db_name = false) {
		if ($db_name) {
			$ret = mysql_select_db($db_name, $this->connexion_);
		} else {
			$ret = mysql_select_db($this->db_name_, $this->connexion_);
		}
		
		if (!$ret) {
			$this->error();
		}
	}
	
	/**
	 * Ferme la connexion à la BD.
	 */
	public function fermer()
	{
		if (!$this->estConnecte()) {
			return;
		}
		
		$ret = mysql_close($this->connexion_);
		if (!$ret) {
			$this->error();
		}
		
		$this->connexion_ = false;
	}

	/**
	 * Envoyer une requête SQL à la base de données.
	 * 
	 * @param sql Texte de la requête.
	 */
	public function query($sql)
	{
		if (!$this->connexion_) {
			$this->connecter();
		}
		
		$this->derniere_requete_ = mysql_query($sql);
		if ($this->derniere_requete_ === false) {
			$this->error();
		}
	}
	
	/**
	 * Protège une chaîne de texte en protégeant les caractères
	 * spéciaux.
	 * 
	 * @param text Chaîne à protéger.
	 * @return Chaîne protégée.
	 */
	public function escape($text) {
		if (!$this->connexion_) {
			$this->connecter();
		}
		
		return mysql_real_escape_string($text, $this->connexion_);
	}
	
	/**
	 * Donne l'ID généré par la dernière requête effectuée. L'ID est la
	 * valeur d'une des colonnes AUTO_INCREMENT de la table.
	 * 
	 * @return L'ID.
	 */
	public function dernierID() {
		return mysql_insert_id($this->connexion_);
	}
	
	/**
	 * Obtient la prochaine entrée de la dernière requête.
	 * 
	 * @return Tableau associatif colonne => valeur pour cette entrée,
	 *         ou false s'il n'y a plus de rangées.
	 * 
	 */
	public function fetch()
	{
		return mysql_fetch_assoc($this->derniere_requete_);
	}
	
	/**
	 * Retourne un tableau contenant tous les résultats de la dernière
	 * requête.
	 * 
	 * @return Tableau contenant un tableau associatif pour chacune des
	 * rangées.
	 */
	 public function fetchAll() {
			$res = array();
			
			while ($cur = $this->fetch()) {
					$res[] = $cur;
			}
			
			return $res;
	 }
	
	/**
	 * Indique si la connexion à la base de données est active.
	 * 
	 * @return True si la connexion est active.
	 */
	public function estConnecte() {
		return $this->connexion_ !== false;
	}
	
	/**
	 * Donne le nombre de résultats dans l'ensemble de résultats.
	 * 
	 * @return Le nombre de résultats.
	 */
	public function nombreResultat()
	{
		return mysql_num_rows($this->derniere_requete_);
	}
	
	/**
	 * Fonction à appeler lorsqu'il y a une error non récupérable.
	 * 
	 * @throw MySQLException
	 */
	public function error()
	{
		throw new MySQLException(mysql_error(), mysql_errno());
	}
	
	/* Descripteur de connexion */
	private $connexion_ = false;
	
	/* Résultat de la dernière requête */
	private $derniere_requete_ = false;
	
	/* Database access */
	private $server_;
	private $db_name_;
	private $username_;
	private $password_;
}

/**
 * Exception lancée lorsqu'il y a une error irrécupérable lors d'un
 * échange avec la base de données.
 */
class MySQLException extends Exception {
		public function __toString() {
				return "Erreur MySQL: [{$this->code}] {$this->message}";
		}
}

?>
