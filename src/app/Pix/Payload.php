<?php
namespace App\Pix;

class Payload {
    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * @var String
     * Chave Pix
     */
    private $pixKey;

    /**
     * @var String
     * Descricao do pagamento
     */
    private $description;


    /**
     * @var String
     * Titular da Compra
     */
    private $merchantName;

    /**
     * @var String
     * Cidade do titular
     */
    private $merchantCity;

    /**
     * @var String
     * Id da tansacao
     */
    private $txid;

    /**
     * @var String
     * Valor da tansacao
     */
    private $amount;


    /**
     * @param String
     * Definir o valor do pix Key
     */
    public function setPixKey($pixKey){
        $this->pixKey = $pixKey;
        return $this;
    }
    /**
     * @param String
     * Metodo definir o valor do descricao da tansacao
     */
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }
    /**
     * @param String
     * Metodo definir Nome do titular da compra
     */
    public function setMerchantName($merchantName){
        $this->merchantName = $merchantName;
        return $this;
    }
    /**
     * @param String
     * Metodo definir a cidade do titular
     */
    public function setMerchantCity($merchantCity){
        $this->merchantCity = $merchantCity;
        return $this;
    }
    /**
     * @param String
     * Metodo definir o Id da tansacao
     */
    public function setTxid($txid){
        $this->txid = $txid;
        return $this;
    }
    /**
     * @param Float
     * Metodo definir o valor da compra
     */
    public function setAmount($amount){
        $this->amount = (string)number_format($amount,2,".","");
        return $this;
    }

    /**
     * @param String
     * Metodo definir o codigo da cidade ou Zip Code
     */
    /*
    public function setCountryCode($countryCode){
        $this->countryCode = $countryCode;
        return $this;
    }
    */

    /**
     * Metodo responsavel por retornar o valor completo de um objecto do payload
     * @return String $id
     * @return String $value
     * @return String $id,$size,$value
     */
    public function getValue($id,$value)
    {
        $size = str_pad(strlen($value),2,"0",STR_PAD_LEFT);
        return $id.$size.$value;
    }

    /**
     * @return String
     * Metodo responsavel por retornar os valores completos da informacao da conta
     */
    public function getMerchantInformation()
    {   //DOMINIO DO BANCO OU IDENTIFICADOR DO BANCO
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,'br.gov.bcb.pix');
        // CHAVE PIX
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY,$this->pixKey);
        //DESCRICAO DO PAGAMENTO
        $description =strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION,$this->description) :"";

        //VALOR COMPLETO
        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION,$gui.$key.$description);

    }

    /**
     * @return String
     * Metodo responsavel por retornar os valores completos do campu adicional do pix
     */
    public function getAddicionalDataFieldTemplate()
    {
        //TXID
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID,$this->txid);

        //RETORNA O VALOR COMPLETO
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE,$txid);
    }

    /**
     * @return String
     * Metodo responsavel por gerar o codigo completo do payload Pix
     */
    public function getPayload()
    {
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,"01").
        $this->getMerchantInformation().$this->getValue(self::ID_MERCHANT_CATEGORY_CODE,"0000").
        $this->getValue(self::ID_TRANSACTION_CURRENCY,"986").$this->getValue(self::ID_TRANSACTION_AMOUNT,$this->amount).
        $this->getValue(self::ID_COUNTRY_CODE,"BR").$this->getValue(self::ID_MERCHANT_NAME,$this->merchantName).
        $this->getValue(self::ID_MERCHANT_CITY,$this->merchantCity).$this->getAddicionalDataFieldTemplate();

        //RETORNA PAYLOAD E CRC16
        return $payload.$this->getCRC16($payload);
    }


    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload) {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

}