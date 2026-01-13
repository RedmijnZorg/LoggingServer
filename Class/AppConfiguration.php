<?php

class AppConfiguration {

    private $database;

    public function __construct(mysqli $database) {
        $this->database = $database;
    }

    /**
     * @param string $item
     * @param string $value
     * @return mixed
     */
    public function setConfiguration($item, $value) {
        $item = $this->database->real_escape_string($item);
        $value = $this->database->real_escape_string($value);

        $findExisting = $this->database->query("SELECT `itemid` FROM `configuration` WHERE `item` = '$item'");
        if ($findExisting->num_rows > 0) {
            $existing = $findExisting->fetch_assoc();
            $existingid = $existing['itemid'];
            return $this->database->query("UPDATE `configuration` SET `value` = '$value' WHERE `itemid` = '$existingid'");
        } else
            return $this->database->query("INSERT INTO `configuration` (`item`, `value`) VALUES ('$item', '$value')");
    }

    /**
     * @param string $item
     * @return string
     */
    public function getConfiguration($item) {
        $item = $this->database->real_escape_string($item);
        $findExisting = $this->database->query("SELECT `value` FROM `configuration` WHERE `item` = '$item'");
        if ($findExisting->num_rows > 0) {
            $existing = $findExisting->fetch_assoc();
            return $existing['value'];
        } else {
            return "";
        }
    }

    /**
     * @param int $itemid
     * @return array|false
     */
    public function getConfigurationDetails($itemid) {
        $itemid = $this->database->real_escape_string($itemid);
        $findExisting = $this->database->query("SELECT * FROM `configuration` WHERE `itemid` = '$itemid'");
        if ($findExisting->num_rows > 0) {
            $existing = $findExisting->fetch_assoc();
            return $existing;
        } else {
            return false;
        }
    }

    /**
     * @param int $itemid
     * @return bool
     */
    public function removeConfiguration($itemid) {
        $itemid = $this->database->real_escape_string($itemid);
        return $this->database->query("DELETE FROM `configuration` WHERE `itemid` = '$itemid'");
    }

    /**
     * @return array
     */
    public function getConfigurations() {
        $findExisting = $this->database->query("SELECT `itemid` FROM `configuration` ORDER BY `item` ASC");
        $returnArray = array();
        if ($findExisting->num_rows > 0) {
            while ($row = $findExisting->fetch_assoc()) {
                $returnArray[] = $this->getConfigurationDetails($row["itemid"]);
            }
        }
        return $returnArray;
    }

}