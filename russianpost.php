<?php

namespace RussianPost {

	const SERVICE_URI = 'http://voh.russianpost.ru:8080/niips-operationhistory-web/OperationHistory';

	const OPERATION_DELIVERY = 'Вручение';
	const OPERATION_PROCESSING = 'Обработка';
	const OPERATION_RECEPTION = 'Приём';
	const OPERATION_RETURN = 'Возврат';

	const OPERATION_ATTR_DELIVERED = 'Прибыло в место вручения';

	abstract class Struct {
		/**
		 * @static
		 * @var string[]
		 * @access protected
		 */
		protected static $classAliases = [];
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			if ($data) {
				foreach ($data as $property => $value) {
					if (is_array($value) && array_key_exists($property, static::$classAliases)) {
						$class = static::$classAliases[$property];
						array_walk($value, function(&$field) use ($class) {$field = new $class($field);});
					} elseif ($value instanceof \stdClass) {
						$class = array_key_exists($property, static::$classAliases)
							? static::$classAliases[$property]
							: get_class($this->$property);
						$value = new $class($value);
					} elseif (is_int($this->$property)) {
						$value = intval($value);
					} elseif (is_bool($this->$property)) {
						$value = !!$value;
					} else {
						$value = strval($value);
					}
					$this->$property = $value;
				}
			}
		}
	}

	/**
	 * авторизационные данные ИС-потребителей данных
	 */
	class AuthorizationHeader extends Struct {
		/**
		 * @var string
		 * @access public
		 */
		public $login = '';
		/**
		 * @var string
		 * @access public
		 */
		public $password = '';
		/**
		 * признак обязательности передачи заголовка сообщения через SOAP Proxy
		 *
		 * @var bool
		 * @access public
		 */
		public $mustUnderstand = false;
	}

	class UpdateOperationRequest extends Struct {
		/**
		 * @var string
		 * @access public
		 */
		public $RequestType = '';
		/**
		 * @var OperationHistoryRecord
		 * @access public
		 */
		public $SourceOperation = null;
		/**
		 * @var OperationHistoryRecord
		 * @access public
		 */
		public $TargetOperation = null;
		/**
		 * @var string
		 * @access public
		 */
		public $ReasonDescription = '';
		/**
		 * @var int
		 * @access public
		 */
		public $InitiatorDepartment = 0;
		/**
		 * @var string
		 * @access public
		 */
		public $ExecutorIP = '';
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->SourceOperation = new OperationHistoryRecord();
			$this->TargetOperation = new OperationHistoryRecord();
			parent::__construct($data);
		}
	}

	/**
	 * данные об операциях над РПО
	 */
	class OperationHistoryData extends Struct {
		/**
		 * @var OperationHistoryRecord[]
		 * @access public
		 */
		public $historyRecord = [];
		/**
		 * @static
		 * @var string[]
		 * @access protected
		 */
		protected static $classAliases = ['historyRecord' => 'RussianPost\OperationHistoryRecord'];
	}

	/**
	 * данные об одной операции над РПО
	 */
	class OperationHistoryRecord extends Struct {
		/**
		 * @var AddressParameters
		 * @access public
		 */
		public $AddressParameters = null;
		/**
		 * @var FinanceParameters
		 * @access public
		 */
		public $FinanceParameters = null;
		/**
		 * @var ItemParameters
		 * @access public
		 */
		public $ItemParameters = null;
		/**
		 * @var OperationParameters
		 * @access public
		 */
		public $OperationParameters = null;
		/**
		 * @var UserParameters
		 * @access public
		 */
		public $UserParameters = null;
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->AddressParameters = new AddressParameters();
			$this->FinanceParameters = new FinanceParameters();
			$this->ItemParameters = new ItemParameters();
			$this->OperationParameters = new OperationParameters();
			$this->UserParameters = new UserParameters();
			parent::__construct($data);
		}
	}

	/**
	 * адресные данные с операцией над РПО
	 */
	class AddressParameters extends Struct {
		/**
		 * адресные данные места назначения пересылки РПО
		 *
		 * @var Address
		 * @access public
		 */
		public $DestinationAddress = null;
		/**
		 * адресные данные места проведения операции над РПО
		 *
		 * @var Address
		 * @access public
		 */
		public $OperationAddress = null;
		/**
		 * данные о стране места назначения пересылки РПО
		 *
		 * @var Country
		 * @access public
		 */
		public $MailDirect = null;
		/**
		 * данные о стране приема РПО
		 *
		 * @var Country
		 * @access public
		 */
		public $CountryFrom = null;
		/**
		 * данные о стране проведения операции РПО
		 *
		 * @var Country
		 * @access public
		 */
		public $CountryOper = null;
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->DestinationAddress = new Address();
			$this->OperationAddress = new Address();
			$this->MailDirect = new Country();
			$this->CountryFrom = new Country();
			$this->CountryOper = new Country();
			parent::__construct($data);
		}
	}

	/**
	 * финансовые данные, связанные с операцией над РПО
	 */
	class FinanceParameters extends Struct {
		/**
		 * сумма наложенного платежа в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $Payment = 0;
		/**
		 * сумма объявленной ценности в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $Value = 0;
		/**
		 * общая сумма платы за пересылку наземным и воздушным транспортом в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $MassRate = 0;
		/**
		 * сумма платы за объявленную ценность в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $InsrRate = 0;
		/**
		 * сумма платы за пересылку воздушным транспортом из общей суммы платы за пересылку в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $AirRate = 0;
		/**
		 * сумма дополнительного тарифного сбора в копейках
		 *
		 * @var int
		 * @access public
		 */
		public $Rate = 0;
	}

	/**
	 * данные о РПО
	 */
	class ItemParameters extends Struct {
		/**
		 * ШИ РПО, текущий для данной операции
		 *
		 * @var string
		 * @access public
		 */
		public $Barcode = '';
		/**
		 * служебная информация, идентифицирующую РПО (ШИ, квитанция, связанная с отправлением и т.п.)
		 *
		 * @var string
		 * @access public
		 */
		public $Internum = '';
		/**
		 * признак корректности вида и категории РПО для внутренней пересылки
		 *
		 * @var bool
		 * @access public
		 */
		public $ValidRuType = false;
		/**
		 * признак корректности вида и категории РПО для международной пересылки
		 *
		 * @var bool
		 * @access public
		 */
		public $ValidEnType = false;
		/**
		 * текстовое описание вида и категории РПО
		 *
		 * @var string
		 * @access public
		 */
		public $ComplexItemName = '';
		/**
		 * данные о разряде РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $MailRank = null;
		/**
		 * данные об отметках РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $PostMark = null;
		/**
		 * данные о виде РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $MailType = null;
		/**
		 * данные о категории
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $MailCtg = null;
		/**
		 * вес РПО в граммах
		 *
		 * @var int
		 * @access public
		 */
		public $Mass = 0;
		/**
		 * значение максимально возможного веса для данного вида и категории РПО для внутренней пересылки
		 *
		 * @var int
		 * @access public
		 */
		public $MaxMassRU = 0;
		/**
		 * значение максимально возможного веса для данного вида и категории РПО для международной пересылки
		 *
		 * @var int
		 * @access public
		 */
		public $MaxMassEN = 0;
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->MailRank = new Rtm02Parameter();
			$this->PostMark = new Rtm02Parameter();
			$this->MailType = new Rtm02Parameter();
			$this->MailCtg = new Rtm02Parameter();
			parent::__construct($data);
		}
	}

	/**
	 * параметры операции над РПО
	 */
	class OperationParameters extends Struct {
		/**
		 * данные о типе операции над РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $OperType = null;
		/**
		 * данные об атрибутах операции над РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $OperAttr = null;
		/**
		 * данные о дате и времени проведения операции над РПО
		 *
		 * @var string
		 * @access public
		 */
		public $OperDate = '';
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->OperType = new Rtm02Parameter();
			$this->OperAttr = new Rtm02Parameter();
			parent::__construct($data);
		}
	}

	/**
	 * данные субъектов связанных с операцией над РПО
	 */
	class UserParameters extends Struct {
		/**
		 * данные о категории отправителя РПО
		 *
		 * @var Rtm02Parameter
		 * @access public
		 */
		public $SendCtg = null;
		/**
		 * данные об отправителе РПО
		 *
		 * @var string
		 * @access public
		 */
		public $Sndr = '';
		/**
		 * данные о получателе РПО
		 *
		 * @var string
		 * @access public
		 */
		public $Rcpn = '';
		/**
		 * @constructor
		 * @param \stdClass $data
		 * @access public
		 */
		public function __construct(\stdClass $data = null) {
			$this->SendCtg = new Rtm02Parameter();
			parent::__construct($data);
		}
	}

	/**
	 * данные о стране
	 */
	class Country extends Struct {
		/**
		 * код страны
		 *
		 * @var int
		 * @access public
		 */
		public $Id = 0;
		/**
		 * двухбуквенный идентификатор страны
		 *
		 * @var string
		 * @access public
		 */
		public $Code2A = '';
		/**
		 * трехбуквенный идентификатор страны
		 *
		 * @var string
		 * @access public
		 */
		public $Code3A = '';
		/**
		 * российское название страны
		 *
		 * @var string
		 * @access public
		 */
		public $NameRU = '';
		/**
		 * международное название страны
		 *
		 * @var string
		 * @access public
		 */
		public $NameEN = '';
	}

	/**
	 * данные
	 */
	class Rtm02Parameter extends Struct {
		/**
		 * код элемента
		 *
		 * @var int
		 * @access public
		 */
		public $Id = 0;
		/**
		 * название элемента
		 *
		 * @var string
		 * @access public
		 */
		public $Name = '';
	}

	/**
	 * адресные данные
	 */
	class Address extends Struct {
		/**
		 * индекс места
		 *
		 * @var string
		 * @access public
		 */
		public $Index = '';
		/**
		 * адрес и/или название места
		 *
		 * @var string
		 * @access public
		 */
		public $Description = '';
	}

	/**
	 * данные запроса истории операций над РПО данных
	 */
	class OperationHistoryRequest extends Struct {
		/**
		 * ШИ отправления, историю операций над которым необходимо получить
		 *
		 * @var string
		 * @access public
		 */
		public $Barcode = '';
		/**
		 * имя ИС-потребителя данных
		 *
		 * @var int
		 * @access public
		 */
		public $MessageType = 0;
	}


	/**
	 * @return \SoapClient
	 */
	function CreateSOAPClient() {
		return new \SoapClient(SERVICE_URI . '?wsdl', ['location' => SERVICE_URI, 'connection_timeout' => 120]);
	}

	/**
	 * @param string $operation
	 * @param OperationHistoryRequest $historyRequest
	 * @param \SoapClient $client
	 * @return OperationHistoryData
	 */
	function CallHistoryOperation($operation, OperationHistoryRequest $historyRequest, \SoapClient $client = null) {
		$client or ($client = CreateSOAPClient());

		$attempts = 10;
		$response = null;

		do {
			--$attempts;
			try {
				$response = $client->__soapCall($operation, [new \SoapParam($historyRequest, 'historyRequest')]);
			} catch (\Exception $e) {
				sleep(5);
			}
		} while ((!$response || ($response instanceof \SoapFault)) && $attempts);

		return new OperationHistoryData($response);
	}

	/**
	 * @param OperationHistoryRequest $historyRequest
	 * @param \SoapClient $client
	 * @return OperationHistoryData
	 */
	function GetOperationHistory(OperationHistoryRequest $historyRequest, \SoapClient $client = null) {
		return CallHistoryOperation('GetOperationHistory', $historyRequest, $client);
	}

	/**
	 * @param OperationHistoryRequest $historyRequest
	 * @param \SoapClient $client
	 * @return OperationHistoryData
	 */
	function UpdateOperationData(OperationHistoryRequest $historyRequest, \SoapClient $client = null) {
		return CallHistoryOperation('UpdateOperationData', $historyRequest, $client);
	}
}