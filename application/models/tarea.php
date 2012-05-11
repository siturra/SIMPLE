<?php

class Tarea extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('identificador');
        $this->hasColumn('inicial');
        $this->hasColumn('final');
        $this->hasColumn('proceso_id');
        $this->hasColumn('nombre');
        $this->hasColumn('posx');
        $this->hasColumn('posy');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Proceso',array(
            'local'=>'proceso_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Conexion as ConexionesOrigen',array(
            'local'=>'id',
            'foreign'=>'tarea_id_origen'
        ));
        
        $this->hasMany('Conexion as ConexionesDestino',array(
            'local'=>'id',
            'foreign'=>'tarea_id_destino'
        ));
        
        $this->hasMany('GrupoUsuarios as GruposUsuarios',array(
            'local'=>'tarea_id',
            'foreign'=>'grupo_usuarios_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));
        
        $this->hasMany('paso as Pasos',array(
            'local'=>'id',
            'foreign'=>'tarea_id',
            'orderBy'=>'orden'
        ));
    }

    public function hasGrupoUsuarios($grupo_id){
        foreach($this->GruposUsuarios as $g)
            if($g->id==$grupo_id)
                return true;
            
        return false;
    }
    
    public function setGruposUsuariosFromArray($grupos_usuarios_ids){        
        //Limpiamos la lista antigua
        foreach($this->GruposUsuarios as $key=>$val)
            unset($this->GruposUsuarios[$key]);
        
        //Agregamos los nuevos
        if(is_array($grupos_usuarios_ids))
            foreach($grupos_usuarios_ids as $g)
                $this->GruposUsuarios[]=Doctrine::getTable('GrupoUsuarios')->find($g);
    }
    
    public function setPasosFromArray($pasos_array){        
        //Limpiamos la lista antigua
        foreach($this->Pasos as $key=>$val)
            unset($this->Pasos[$key]);
        
        //Agregamos los nuevos
        if(is_array($pasos_array)){
            foreach($pasos_array as $key=>$p){
                $paso=new Paso();
                $paso->orden=$key;
                $paso->modo=$p['modo'];
                $paso->formulario_id=$p['formulario_id'];
                $this->Pasos[]=$paso;
            }
        }
    }
    
}
