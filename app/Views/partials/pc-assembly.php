<div class="pc-assembly" data-pc-assembly aria-hidden="true">
  <div class="pc-position"><div class="pc-scene">
    <div class="pc-wires">
      <?php foreach (['left', 'right'] as $side): ?>
        <div class="pc-wires-<?= $side ?>">
          <?php for ($i = 0; $i < 5; $i++): ?><i><b></b></i><?php endfor ?>
        </div>
      <?php endforeach ?>
    </div>
    <div class="pc-rig">
      <div class="pc-board">
        <span class="pc-socket"></span><span class="pc-io"></span>
        <span class="pc-regulators"></span><span class="pc-dimm-slots"></span>
        <span class="pc-pcie"></span><span class="pc-chipset"></span>
      </div>
      <div class="pc-chip"><span class="pc-chip-base"></span><span class="pc-chip-lid"></span></div>
      <div class="pc-ram"></div><div class="pc-ram pc-ram-second"></div>
      <div class="pc-cooler"><span class="pc-fins"></span><span class="pc-fan"></span></div>
      <div class="pc-case">
        <span class="pc-tray"></span><span class="pc-roof"></span><span class="pc-floor"></span>
        <span class="pc-front"><i class="pc-fan"></i><i class="pc-fan"></i></span>
        <span class="pc-glass"></span>
      </div>
    </div>
  </div></div>
</div>
