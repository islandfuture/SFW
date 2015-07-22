<div class="panel panel-primary">
    <div class="panel-heading"><:classname:>
        &rarr; найдено: <?=$this->iPageSize < $this->iCntModels ? $this->iPageSize : $this->iCntModels?> из <?=$this->iCntModels; ?>
    </div>
    <div class="panel-body">
        <table class="table table-hover">
            <thead>
                <tr>
<:sort_fields:>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($this->arItems as $iPos => $oModel): ?>
                <tr valign="top">
<:value_fields:>
                </tr>
                <?php 
                endforeach; ?>
            </tbody>
        </table>

        <div class="sfw_paging">
        <?php
        if ($this->paging ):
            echo \IslandFuture\Sfw\Application::one()->block(
                'sfw.paging',
                array(
                    'iSize'=>$this->iPageSize,
                    'iCurrent'=>$this->iPage,
                    'iTotal'=> $this->iCntModels
                ),
                array(
                    'tempalte' => 'default'
                )
            );
        endif;
        ?>
        </div>

    </div>

</div>
