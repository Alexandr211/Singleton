<?php

namespace Singleton\RealWorld;

class Singleton
{
    
    private static $instances = [];

    /**
     * Конструктор Одиночки не должен быть публичным. Однако он не может быть
     * приватным, если мы хотим разрешить создание подклассов.
     */
    protected function __construct() { }

    /**
     * Клонирование и десериализация не разрешены для одиночек.
     */
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Метод, используемый для получения экземпляра Одиночки.
     */
    public static function getInstance()
    {
        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            // Ключевое слово "static" вместо фактического имени класса. В этом контексте
            // означает «имя текущего класса». Когда метод вызывается в
            // подклассе, мы хотим, чтобы экземпляр этого подкласса был создан
            // здесь.
           
            self::$instances[$subclass] = new static;
        }
        return self::$instances[$subclass];
    }
}


class Logger extends Singleton
{
    /**
     * Ресурс указателя файла журнала.
     */
    private $fileHandle;

    /**
     * Поскольку конструктор Одиночки вызывается только один раз, постоянно
     * открыт всего лишь один файловый ресурс.
     *
     * для простоты открываем здесь консольный поток
     * вместо фактического файла.
     */
    protected function __construct()
    {
        $this->fileHandle = fopen('php://stdout', 'w');
    }

    /**
     * Пишем запись в журнале в открытый файловый ресурс.
     */
    public function writeLog(string $message): void
    {
        $date = date('Y-m-d');
        fwrite($this->fileHandle, "$date: $message\n");
    }

    /**
     * Просто удобный ярлык для уменьшения объёма кода, необходимого для
     * регистрации сообщений из клиентского кода.
     */
    public static function log(string $message): void
    {
        $logger = static::getInstance();
        $logger->writeLog($message);
    }
}

/** 
 * Если требуется получить доступ к настройкам приложений из самых разных мест
 * программы. 
 */
class Config extends Singleton
{
    private $hashmap = [];

    public function getValue(string $key): string
    {
        return $this->hashmap[$key];
    }

    public function setValue(string $key, string $value): void
    {
        $this->hashmap[$key] = $value;
    }
}

/**
 * Клиентский код.
 */
Logger::log("Started!");

// Сравниваем значения одиночки-Логгера.
$l1 = Logger::getInstance();
$l2 = Logger::getInstance();
if ($l1 === $l2) {
    Logger::log("Logger has a single instance.");
} else {
    Logger::log("Loggers are different.");
}

// Проверяем, как одиночка-Конфигурация сохраняет данные...
$config1 = Config::getInstance();
$login = "test_login";
$password = "test_password";
$config1->setValue("login", $login);
$config1->setValue("password", $password);
// ...и восстанавливает их.
$config2 = Config::getInstance();
if ($login == $config2->getValue("login") &&
    $password == $config2->getValue("password")
) {
    Logger::log("Config singleton also works fine.");
}

Logger::log("Finished!");
