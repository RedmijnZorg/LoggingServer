<?php
/**
Beheer van configuratie
**/
class AppConfiguration {

    private $database;

    public function __construct(mysqli $database) {
        $this->database = $database;
    }

    /**
     * Stelt een configuratieitem in
     *
     * @param string $item
     * @param string $value
     * @return mixed
     */
    public function setConfiguration(string $item, string $value) {
        $item = $this->database->real_escape_string($item);
        $value = $this->database->real_escape_string($value);

		// Het configuratieitem opzoeken
        $findExisting = $this->database->query("SELECT `itemid` FROM `configuration` WHERE `item` = '$item'");
        if ($findExisting->num_rows > 0) {
        	// Bestaat deze al? Bewerk dan de waarde
            $existing = $findExisting->fetch_assoc();
            $existingid = $existing['itemid'];
            return $this->database->query("UPDATE `configuration` SET `value` = '$value' WHERE `itemid` = '$existingid'");
        } else
        	// Bestaat deze niet? Maak dan een nieuw item aan
            return $this->database->query("INSERT INTO `configuration` (`item`, `value`) VALUES ('$item', '$value')");
    }

    /**
     * Haalt de waarde van een configuratieitem op
     *
     * @param string $item
     * @return string
     */
    public function getConfiguration(string $item) {
        $item = $this->database->real_escape_string($item);
        // Het configuratieitem opzoeken
        $findExisting = $this->database->query("SELECT `value` FROM `configuration` WHERE `item` = '$item'");
        if ($findExisting->num_rows > 0) {
        	// Bestaat deze? Geef de waarde terug
            $existing = $findExisting->fetch_assoc();
            return $existing['value'];
        } else {
        	// Bestaat deze niet? Geef een lege waarde terug
            return "";
        }
    }

    /**
     * Haalt de details van een configuratieitem op
     *
     * @param int $itemid
     * @return array|false
     */
    public function getConfigurationDetails(int $itemid) {
        $itemid = $this->database->real_escape_string($itemid);
        // Het configuratieitem opzoeken
        $findExisting = $this->database->query("SELECT * FROM `configuration` WHERE `itemid` = '$itemid'");
        if ($findExisting->num_rows > 0) {
        	// Bestaat deze? Geef de details terug
            $existing = $findExisting->fetch_assoc();
            return $existing;
        } else {
        	// Bestaat deze niet? Geef 'false' terug
            return false;
        }
    }

    /**
     * Verwijdert een configuratieitem
     *
     * @param int $itemid
     * @return bool
     */
    public function removeConfiguration(int $itemid) {
        $itemid = $this->database->real_escape_string($itemid);
        return $this->database->query("DELETE FROM `configuration` WHERE `itemid` = '$itemid'");
    }

    /**
     * Haalt alle configuratieitems op
     *
     * @return array
     */
    public function getConfigurations() {
    	// Alle configuratieitems opzoeken
        $findExisting = $this->database->query("SELECT `itemid` FROM `configuration` ORDER BY `item` ASC");
        $returnArray = array();
        if ($findExisting->num_rows > 0) {
            while ($row = $findExisting->fetch_assoc()) {
            	// Haal de details op en voeg ze toe aan de array
                $returnArray[] = $this->getConfigurationDetails($row["itemid"]);
            }
        }
        return $returnArray;
    }

}