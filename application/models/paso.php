<?php

class Paso extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('orden');
        $this->hasColumn('modo');
        $this->hasColumn('formulario_id');
        $this->hasColumn('tarea_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Formulario',array(
            'local'=>'formulario_id',
            'foreign'=>'id'
        ));
        
        $this->hasOne('Tarea',array(
            'local'=>'tarea_id',
            'foreign'=>'id'
        ));
    }

}
