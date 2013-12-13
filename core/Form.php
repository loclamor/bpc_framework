<?php

class Form {
    
    private $clazz;
    private $form = "";
    
    /**
     * Automatic generation of HTML form for a specified Entite.
     * @param Entite $ent
     */
    function __construct(Entite $ent) {
        
        $this->clazz = get_class( $ent );
        
        // add the hidden ID if its not defined
        if( !in_array('id', $ent->memberType) ) {
            $this->addInputHidden('id', $ent->id );
        }
        
        foreach ( $ent->memberType as $member => $type ){
            
            $get = "get" . ucfirst( $member );
            
            // Type could be : varchar(length), text, date, integer, hidden
            switch ( strtolower( $type ) ) {
                case "hidden" :
                    $this->addInputHidden( $member, $ent->$get() );
                    break;
                case "date" :
                    $this->addInputDate( $member, $ent->$get() );
                    break;
                case "integer" :
                    $this->addInputNumber( $member, $ent->$get() );
                    break;
                default:
                    //special case for varchar
                    if( strtolower( substr( $type, 0, 7 ) ) == 'varchar(' ) {
                        $length = substr( $type, 7, -1 );
                        $this->addInputText( $member, $ent->$get(), $length );
                    }
                    break;
            }
        }
    }
    
    public function getForm( $action = "", $methode = "POST" ) {
        return '<form action="' . $action . '" method="' . $methode . '" >' . $this->form . '<input type="submit" value="Valider"/></form>';
    }
    
    private function addInputHidden( $name, $value ) {
        $this->form .= '<input type="hidden" name="' . $name . '" id="' . $this->clazz . '_' . $name .'" value="' . $value . '" />';
    }
    
    private function addInputDate( $name, $value ) {
        $this->form .= '<input type="date" name="' . $name . '" id="' . $this->clazz . '_' . $name .'" value="' . $value . '" /><br/>';
    }
    
    private function addInputNumber( $name, $value ) {
        $this->form .= '<input type="number" name="' . $name . '" id="' . $this->clazz . '_' . $name .'" value="' . $value . '" /><br/>';
    }
    
    private function addInputText( $name, $value, $length = 255 ) {
        $this->form .= '<input type="text" name="' . $name . '" id="' . $this->clazz . '_' . $name .'" value="' . $value . '" maxlength="' . $length . '" /><br/>';
    }
}
