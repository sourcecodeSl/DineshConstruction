<?php

class Gatepass
{
    public $id;
    public $gatepass_code;
    public $invoice_id;
    public $name;
    public $address;
    public $id_number;
    public $equipment_type;
    public $serial_no;
    public $issued_by;
    public $gatepass_date;
    public $created_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `gatepass` WHERE `id` = " . (int)$id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->gatepass_code = $result['gatepass_code'];
                $this->invoice_id = $result['invoice_id'];
                $this->name = $result['name'];
                $this->address = $result['address'];
                $this->id_number = $result['id_number'];
                $this->equipment_type = $result['equipment_type'];
                $this->serial_no = $result['serial_no'];
                $this->issued_by = $result['issued_by'];
                $this->gatepass_date = $result['gatepass_date'];
                $this->created_at = $result['created_at'];
            }
        }
    }

    public function create()
    {
        $db = Database::getInstance();
        $query = "INSERT INTO `gatepass` (
            `gatepass_code`, `invoice_id`, `name`, `address`, `id_number`, `equipment_type`, `serial_no`, `issued_by`, `gatepass_date`
        ) VALUES (
            '" . $db->escapeString($this->gatepass_code) . "', 
            '" . $db->escapeString($this->invoice_id) . "', 
            '" . $db->escapeString($this->name) . "', 
            '" . $db->escapeString($this->address) . "', 
            '" . $db->escapeString($this->id_number) . "', 
            '" . $db->escapeString($this->equipment_type) . "', 
            '" . $db->escapeString($this->serial_no) . "', 
            '" . $db->escapeString($this->issued_by) . "', 
            '" . $db->escapeString($this->gatepass_date) . "'
        )";

        $result = $db->readQuery($query);

        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    public function update()
    {
        $db = Database::getInstance();
        $query = "UPDATE `gatepass` SET 
            `gatepass_code` = '" . $db->escapeString($this->gatepass_code) . "', 
            `invoice_id` = '" . $db->escapeString($this->invoice_id) . "', 
            `name` = '" . $db->escapeString($this->name) . "', 
            `address` = '" . $db->escapeString($this->address) . "', 
            `id_number` = '" . $db->escapeString($this->id_number) . "', 
            `equipment_type` = '" . $db->escapeString($this->equipment_type) . "', 
            `serial_no` = '" . $db->escapeString($this->serial_no) . "', 
            `issued_by` = '" . $db->escapeString($this->issued_by) . "', 
            `gatepass_date` = '" . $db->escapeString($this->gatepass_date) . "'
            WHERE `id` = '" . (int)$this->id . "'";

        $result = $db->readQuery($query);

        if ($result) {
            return $this->__construct($this->id);
        } else {
            return false;
        }
    }

    public function delete()
    {
        $query = "DELETE FROM `gatepass` WHERE `id` = '" . (int)$this->id . "'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function all()
    {
        $query = "SELECT * FROM `gatepass` ORDER BY `id` DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getByInvoiceId($invoice_id)
    {
        $query = "SELECT * FROM `gatepass` WHERE `invoice_id` = '" . (int)$invoice_id . "' ORDER BY `id` DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function filter($searchTerm = '')
    {
        $db = Database::getInstance();
        $query = "SELECT g.*, er.bill_number 
                  FROM `gatepass` g 
                  LEFT JOIN `equipment_rent` er ON g.invoice_id = er.id 
                  WHERE 1";

        if (!empty($searchTerm)) {
            $search = $db->escapeString($searchTerm);
            $query .= " AND (g.gatepass_code LIKE '%$search%' OR g.name LIKE '%$search%' OR er.bill_number LIKE '%$search%' OR g.invoice_id LIKE '%$search%')";
        }

        $query .= " ORDER BY g.id DESC";
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getItems()
    {
        $GATEPASS_ITEM = new GatepassItem(null);
        return $GATEPASS_ITEM->getByGatepassId($this->id);
    }

    /**
     * Auto-generate a gate pass for a rent bill covering every billed item
     * that is not yet included in an earlier gate pass.
     * Returns the new gate pass id, or false when nothing is left to issue.
     */
    public static function autoCreateForRent($rent_id, $issued_by = '')
    {
        $rent_id = (int) $rent_id;
        $RENT = new EquipmentRent($rent_id);
        if (!$RENT->id || !empty($RENT->is_cancelled)) {
            return false;
        }

        $db = Database::getInstance();

        // Quantities already issued through earlier gate passes
        $issuedMap = [];
        $issuedResult = $db->readQuery("SELECT gi.equipment_id, gi.sub_equipment_id, SUM(gi.quantity) AS total_issued
                                        FROM gatepass_items gi
                                        INNER JOIN gatepass g ON gi.gatepass_id = g.id
                                        WHERE g.invoice_id = $rent_id
                                        GROUP BY gi.equipment_id, gi.sub_equipment_id");
        while ($row = mysqli_fetch_assoc($issuedResult)) {
            $issuedMap[$row['equipment_id'] . '_' . ($row['sub_equipment_id'] ?: 'NULL')] = (float) $row['total_issued'];
        }

        $items = [];
        foreach ((new EquipmentRentItem(null))->getByRentId($rent_id) as $item) {
            $key = $item['equipment_id'] . '_' . ($item['sub_equipment_id'] ?: 'NULL');
            $qty = (float) $item['quantity'];
            // Consume already-issued qty line by line so repeated equipment lines are handled correctly
            $covered = min($qty, $issuedMap[$key] ?? 0);
            $issuedMap[$key] = ($issuedMap[$key] ?? 0) - $covered;
            $available = $qty - $covered;
            if ($available <= 0) {
                continue;
            }
            $items[] = [
                'equipment_id' => $item['equipment_id'],
                'sub_equipment_id' => $item['sub_equipment_id'] ?: null,
                'equipment_name' => $item['equipment_name'],
                'quantity' => $available,
            ];
        }

        if (empty($items)) {
            return false;
        }

        $CUSTOMER = new CustomerMaster($RENT->customer_id);
        $DOCUMENT_TRACKING = new DocumentTracking(1);
        $lastId = (int) ($DOCUMENT_TRACKING->gatepass_id ?? 0);

        $GATEPASS = new Gatepass();
        $GATEPASS->gatepass_code = 'GP/' . ($_SESSION['id'] ?? '0') . '/0' . ($lastId + 1);
        $GATEPASS->invoice_id = $rent_id;
        $GATEPASS->name = $CUSTOMER->name;
        $GATEPASS->address = $CUSTOMER->address;
        $GATEPASS->id_number = $CUSTOMER->nic ?? '';
        $GATEPASS->equipment_type = implode(', ', array_map(function ($i) {
            return $i['equipment_name'] . ' (' . $i['quantity'] . ')';
        }, $items));
        $GATEPASS->serial_no = '';
        $GATEPASS->issued_by = $issued_by;
        $GATEPASS->gatepass_date = date('Y-m-d');

        $gp_id = $GATEPASS->create();
        if (!$gp_id) {
            return false;
        }

        foreach ($items as $item) {
            $GP_ITEM = new GatepassItem(null);
            $GP_ITEM->gatepass_id = $gp_id;
            $GP_ITEM->equipment_id = (int) $item['equipment_id'];
            $GP_ITEM->sub_equipment_id = $item['sub_equipment_id'] ? (int) $item['sub_equipment_id'] : null;
            $GP_ITEM->quantity = $item['quantity'];
            $GP_ITEM->remarks = '';
            $GP_ITEM->create();
        }

        (new DocumentTracking(null))->incrementDocumentId('gatepass');

        return $gp_id;
    }
}
