<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!--
$receiveDateFrom = $meta['receiveDateFrom'];
$receiveDateTo = $meta['receiveDateTo'];
$minDay = $meta['minDay'];
$maxDay = $meta['maxDay'];
$produceMinDay = $meta['produceMinDay'];
$produceMaxDay = $meta['produceMaxDay'];
 -->
<div style="font-size:12px; color:#808080; text-indent:0;">
    <p style="margin: 0;">Estimated delivery time: Production time<?php echo esc_attr( $produceMinDay . "-" . $produceMaxDay)?>working days+transportation time<?php echo esc_attr( $minDay . "-" . $maxDay)?>working days.</p>
    <p style="margin: 0;">Estimated receipt time: <?php echo esc_attr( $receiveDateFrom . "-" . $receiveDateTo)?></p>
</div>