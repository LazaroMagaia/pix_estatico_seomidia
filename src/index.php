<?php
require __DIR__ . '/vendor/autoload.php';

use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use \App\Pix\Payload;

//INSTANCIA PRINCIPAL DO PAYLOAD DO PIX
$payload = new Payload();
$payload->setPixKey("12345678911");//Chave pix ficticia podendo ser CPf ou outra coisa
$payload->setDescription("Pagamento do Pedido 450");
$payload->setMerchantName("Lazaro Magaia");
$payload->setMerchantCity("Maputo");
$payload->setAmount(100.00);
$payload->setTxid("SeoMidia42");

$payload_Qrcode= $payload->getPayload();

//INSTANCIA DO QRCODE
$qrCode = new QrCode($payload_Qrcode);

$output = new Output\Svg();

// Echo an HTML table
$output = new Output\Html();
?>

<h1>Codifgo QR</h1>
<br>
<?= $output->output($qrCode);?>
<br><br>
<h2>Codigo Pix</h2>
<strong> <?= $payload_Qrcode?> </strong>

