<?php
/**
 * Octaverum - AI Müzik Uygulaması
 * Veritabanı Bağlantı Sınıfı
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;
    private $statement;
    
    /**
     * Singleton örnek için private constructor
     */
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Hata durumunu ele al - üretimde detaylı hata mesajı vermemek önemlidir
            if (DEBUG_MODE) {
                die("Veritabanı bağlantı hatası: " . $e->getMessage());
            } else {
                die("Veritabanına bağlanırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
            }
        }
    }
    
    /**
     * Singleton örneğini döndür
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * SQL sorgusu hazırlar
     * @param string $query SQL sorgusu
     * @param array $params Sorgu parametreleri
     * @return Database
     */
    public function query($query, $params = []) {
        $this->statement = $this->connection->prepare($query);
        $this->statement->execute($params);
        return $this;
    }
    
    /**
     * Tek bir kayıt döndürür
     * @return array|false
     */
    public function fetchOne() {
        return $this->statement->fetch();
    }
    
    /**
     * Tüm kayıtları döndürür
     * @return array
     */
    public function fetchAll() {
        return $this->statement->fetchAll();
    }
    
    /**
     * Tek bir değer döndürür (örn. COUNT(*) sorguları için)
     * @return mixed
     */
    public function fetchColumn() {
        return $this->statement->fetchColumn();
    }
    
    /**
     * Etkilenen satır sayısını döndürür
     * @return int
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }
    
    /**
     * Son eklenen kayıt ID'sini döndürür
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Transaction başlatır
     */
    public function beginTransaction() {
        $this->connection->beginTransaction();
    }
    
    /**
     * Transaction'ı onaylar
     */
    public function commit() {
        $this->connection->commit();
    }
    
    /**
     * Transaction'ı geri alır
     */
    public function rollback() {
        $this->connection->rollBack();
    }
    
    /**
     * Tablo ismine prefix ekler
     * @param string $table Tablo adı
     * @return string
     */
    public function getTableName($table) {
        return DB_PREFIX . $table;
    }
    
    /**
     * Veri kaydetme işlemi için yardımcı fonksiyon
     * @param string $table Tablo adı (prefix olmadan)
     * @param array $data Kaydedilecek veri
     * @return int|bool Son eklenen ID ya da false
     */
    public function insert($table, $data) {
        $table = $this->getTableName($table);
        $fields = array_keys($data);
        
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $fieldsStr = implode(', ', $fields);
        $placeholdersStr = implode(', ', $placeholders);
        
        $query = "INSERT INTO $table ($fieldsStr) VALUES ($placeholdersStr)";
        
        try {
            $this->query($query, $data);
            return $this->lastInsertId();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                echo "Veritabanı hata: " . $e->getMessage();
            }
            return false;
        }
    }
    
    /**
     * Veri güncelleme işlemi için yardımcı fonksiyon
     * @param string $table Tablo adı (prefix olmadan)
     * @param array $data Güncellenecek veri
     * @param array $where Koşul
     * @return int|bool Etkilenen satır sayısı ya da false
     */
    public function update($table, $data, $where) {
        $table = $this->getTableName($table);
        
        $setFields = array_map(function($field) {
            return "$field = :$field";
        }, array_keys($data));
        
        $whereFields = array_map(function($field) {
            return "$field = :where_$field";
        }, array_keys($where));
        
        $setStr = implode(', ', $setFields);
        $whereStr = implode(' AND ', $whereFields);
        
        $query = "UPDATE $table SET $setStr WHERE $whereStr";
        
        $params = $data;
        foreach ($where as $field => $value) {
            $params["where_$field"] = $value;
        }
        
        try {
            $this->query($query, $params);
            return $this->rowCount();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                echo "Veritabanı hata: " . $e->getMessage();
            }
            return false;
        }
    }
    
    /**
     * Veri silme işlemi için yardımcı fonksiyon
     * @param string $table Tablo adı (prefix olmadan)
     * @param array $where Koşul
     * @return int|bool Etkilenen satır sayısı ya da false
     */
    public function delete($table, $where) {
        $table = $this->getTableName($table);
        
        $whereFields = array_map(function($field) {
            return "$field = :$field";
        }, array_keys($where));
        
        $whereStr = implode(' AND ', $whereFields);
        
        $query = "DELETE FROM $table WHERE $whereStr";
        
        try {
            $this->query($query, $where);
            return $this->rowCount();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                echo "Veritabanı hata: " . $e->getMessage();
            }
            return false;
        }
    }
    
    /**
     * Veri seçme işlemi için yardımcı fonksiyon
     * @param string $table Tablo adı (prefix olmadan)
     * @param array $where Koşul (isteğe bağlı)
     * @param string|array $fields Seçilecek alanlar (isteğe bağlı)
     * @param string $orderBy Sıralama (isteğe bağlı)
     * @param int $limit Limit (isteğe bağlı)
     * @param int $offset Offset (isteğe bağlı)
     * @return array|bool Sonuç dizisi ya da false
     */
    public function select($table, $where = [], $fields = '*', $orderBy = '', $limit = 0, $offset = 0) {
        $table = $this->getTableName($table);
        
        if (is_array($fields)) {
            $fieldsStr = implode(', ', $fields);
        } else {
            $fieldsStr = $fields;
        }
        
        $query = "SELECT $fieldsStr FROM $table";
        
        $params = [];
        if (!empty($where)) {
            $whereFields = array_map(function($field) {
                return "$field = :$field";
            }, array_keys($where));
            
            $whereStr = implode(' AND ', $whereFields);
            $query .= " WHERE $whereStr";
            $params = $where;
        }
        
        if (!empty($orderBy)) {
            $query .= " ORDER BY $orderBy";
        }
        
        if ($limit > 0) {
            $query .= " LIMIT $limit";
            
            if ($offset > 0) {
                $query .= " OFFSET $offset";
            }
        }
        
        try {
            $this->query($query, $params);
            return $this->fetchAll();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                echo "Veritabanı hata: " . $e->getMessage();
            }
            return false;
        }
    }
}
