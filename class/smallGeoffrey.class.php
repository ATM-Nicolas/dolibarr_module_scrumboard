<?php

class TSmallGeoffrey {

    function __construct($width) {
        
        $this->top = 0;
        $this->width = $width;
        
        $this->TBox = array();
        
        $this->debug = false;
    }

    function addBox($top,$left,$height,$width, $taskid=0, $fk_task_parent=0) {
        
        $box = new stdClass;
        $box->top = $top;
        $box->left = $left;
        $box->height = $height;
        $box->width = $width;
        $box->taskid = $taskid;
        $box->fk_task_parent = $fk_task_parent;
        
        $this->TBox[] = $box;
        
        usort($this->TBox, array('TSmallGeoffrey','sortBoxes'));
        
    }

    function sortBoxes(&$a, &$b) {
        
          if($a->top < $b->top) return -1;
          else if($a->top > $b->top) return 1;
          else {
              
              if($a->left < $b->left) return -1;
              elseif($a->left > $b->left) return 1; 
              else return 0;
          }
        
    }

    function getBoxes($y) { // récupère les boxes à cette hauteur
        $Tab = array();
        foreach($this->TBox as &$box) {
            
            if($box->top<=$y && $box->top + $box->height>$y) {
                $Tab[] = $box;
            }
            
        } 
        
        return $Tab;    
    
    }
        
    function noBoxeHere($y,$x, $TBox = array()) {
        if($this->debug) {
            print '<hr>noBoxeHere('.$y.','.$x.')';
            var_dump($TBox);
        }
        
        if(empty($TBox))$TBox=&$this->TBox;
        
        foreach($TBox as &$box) {
            
            if($box->left<=$x && $box->left + $box->width > $x && $box->top<=$y && $box->top + $box->height>$y ) { // il y a une boite ici
                if($this->debug){ print " y a déjà une boite là !";
                    var_dump($box);}
                return false;
            } 
            
        }
        
        if($this->debug) print "Rien ici !";
        return true;
        
    }   
        
    function isLargeEnougthEmptyPlace($y,$x, $h, $w) {
        if($this->debug) print "isLargeEnougthEmptyPlace($y,$x, $h, $w);";
        
        $y_before = 0;
        $y_after = false;
        $x_before = 0;
        $x_after = $this->width-1;
        
        foreach($this->TBox as &$box) {
            
            $box_xw = $box->left + $box->width;
            
            if( $box_xw > $x && $box->left<=$x ) {
                // boite au dessus ou au dessous ?
                if($box->top + $box->height<=$y && $box->top + $box->height>$y_before)$y_before = $box->top + $box->height;
                else if($box->top > $y && ($box->top <= $y_after || $y_after ===  false) )$y_after = $box->top;
                
            }
            
            if($box->top<$y && $box->top + $box->height<=$y) {
                if($box_xw > $x && $box->left < $x && $box_xw - 1  > $x_before ){
                        $x_before = $box_xw -1; 
                    
                    if($this->debug){
                    print "($box_xw) x_before = $x_before;";
                    var_dump($box);}
                    
                }
                else if($box->left >= $x+$w && $box->left < $x_after)$x_after = $box->left;
            }
                        
            if(( $y_after!==false && $y_after - $y_before < $h) || $x_after - $x_before < $w) {
                if($this->debug) {
                    print "Pas assez grand ($y_before,$x_before => $y_after, $x_after)";
                    
                }
                    
                return false; // pas assez de place
            } 
        }
        if($this->debug) print 1;
        return true;
    }    
        
    function getNextPlace($h, $w) {
        if($this->debug)print " getNextPlace($h, $w)<hr>";
        
        $y = $this->top;
        $x = 0;
        
        if(empty($this->TBox)) return array(0,0);
        
        $cpt_notFinishYet = 0;
         
        $nb_max = (count($this->TBox)+1) * 2;
        if($this->debug)var_dump($this->TBox);
        while(true) {
            
           $TBox = $this->getBoxes($y);
           
           if($this->debug)var_dump($y, $TBox);
           $empty_place = false; 
           $less_next_y = false;
           
           for($x = 0; $x<=$this->width - $w; $x++) { // on parcours la largeur pour voir s'il y a un emplacement
           
               if($this->noBoxeHere($y,$x, $TBox)) {
                       
                  $empty_place = true;
                  
                  if($this->isLargeEnougthEmptyPlace($y,$x, $h, $w)) {
                        if($this->debug) print '...trouvé ('.$y.','.$x.') !<br />';    
                      return array($x,$y); 
                      
                  }
                       
               }
               
           } 
            
           foreach($TBox as &$box) {
               if($less_next_y === false || $less_next_y>$box->top + $box->height)$less_next_y=$box->top + $box->height;
           } 
           
           if(!$empty_place) $this->top = $less_next_y;
           
           if($less_next_y===false || $less_next_y == $y) {
               $y = $y + 1 ;
           }
           else{
               $y = $less_next_y;
           }
          
           if($this->debug) print '$less_next_y : '.$less_next_y.'<br />';
           
           $cpt_notFinishYet++;
           if($cpt_notFinishYet>$nb_max) {
               exit('infini');
           }
           
        }
        
    }
    
}