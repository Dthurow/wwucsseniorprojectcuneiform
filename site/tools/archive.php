<?php
include_once('user.php');

class Archive {

    private $id;
    private $name;
    private $tablets;

    static function addArchive($name, PDO $pdo) {
        $sql = "INSERT INTO `archive` (`archive_id`, `user_id`, `name`) VALUES (NULL, :user_id, :name)";
        $statement = $pdo->prepare($sql);
        if (!$statement->execute([':user_id' => USER::getUserId(), ':name' => $name])) {
            die($statement->errorInfo());
        }
        return new Archive($pdo->lastInsertId(), $pdo);
    }

    public function __construct($id, PDO $pdo) {
        // The name of the archive is probably known when looking up the user's
        // archives, and could should be able to be set without a database call.
        // Since PHP does not allow multiple constructors, this could be changed
        // to a Factory pattern, with static methods that build the Archive from
        // just an id or and id and name. This way is more general, though.
        $this->id = $id;
        $this->fetchArchiveData($pdo); // Sets $this->name;
        $this->tablets = $this->fetchArchiveTablets($pdo);
    }

    public function getID() {
        return $this->id;
    }

    public function  getName() {
        return $this->name;
    }

    private function fetchArchiveData(PDO $pdo) {
        $sql = "SELECT * FROM `archive` WHERE `archive_id` = :archive_id";
        $statement = $pdo->prepare($sql);
        // Bind archive_id to $this->id
        $statement->execute([':archive_id' => $this->id]);
        // Get data, must be only one row
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['name'];
        // While we have the data, verify the logged in user is the owner of the archive
        assert($row['user_id'] == User::getUserId(), "Archive doesn't belong to the logged in user");
    }

    private function fetchArchiveTablets(PDO $pdo) {
        $sql = "SELECT * from `archive_tablet` NATURAL JOIN `tablet` WHERE `archive_id` = :archive_id";
        $statement = $pdo->prepare($sql);
        // Bind archive_id to $this->id
        $statement->execute([':archive_id' => $this->id]);
        // Get data, must be only one row
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            $output[] = $row['name'];
        }
        return $output;
    }

    public function addTablet($tabletID, PDO $pdo) {
        $sql = "INSERT INTO `archive_tablet` (`archive_tablet_id`, `archive_id`, `tablet_id`) VALUES (NULL, :archive_id, :tablet_id)";
        $statement = $pdo->prepare($sql);
        if (!$statement->execute([':archive_id' => $this->id, ':tablet_id' => $tabletID])) {
            die($statement->errorInfo());
        }
        return true;
    }

    public function display() {
        $tablets_count = count($this->tablets);
        ?>
        <li><span class="glyphicon glyphicon-minus-sign list-minimizer"></span> <?php echo "$this->name ($tablets_count)"; ?>
            <ul>
                <?php
                foreach ($this->tablets as $tablet) {
                    echo "<li>$tablet</li>\n";
                }
                ?>
            </ul>
        </li>
        <?php
    }

}
?>
