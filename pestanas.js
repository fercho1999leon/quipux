<!--script language="JavaScript"-->

// JavaScript Document
    function changedepesel(enviara)
    {
        document.form1.codTx.value = enviara;
        envioTx();
    }

    function mostrar_botones(estado,carpeta,tiporad)
    {
//alert (estado+' - '+carpeta);
        if (estado!=1)
        document.getElementById('btn_comentar').style.display = '';        
          
        if ('<?=$pagactual?>'=='1') document.getElementById('btn_imgRegresar').style.display = '';

        if (estado==0) {  //Archivados
            document.getElementById('btn_informar').style.display = '';
            if(carpeta!=0) document.getElementById('btn_noarchivar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            document.getElementById('btn_asociar_documento').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
        }
        if (estado==1) {  //En edicion            
            document.getElementById('btn_eliminar').style.display = '';
            document.getElementById('btn_corregir').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_editar').style.display = '';
            document.getElementById('btn_informar').style.display = '';
            document.getElementById('btn_firmar').style.display = '';            
            if (tiporad==2)
            document.getElementById('btn_enviarFisico').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
            document.getElementById('btn_asociar_documento').style.display = '';
            document.getElementById('btn_tarea_asignar').style.display = '';
         }
        if (estado==2) {  //Recibidos
            document.getElementById('btn_tramitar').style.display = '';
            if ('<?=$pagactual?>'=='1'){ document.getElementById('btn_responder').style.display = '';
            document.getElementById('btn_responderTodos').style.display = '';
            }
            document.getElementById('btn_informar').style.display = '';
            document.getElementById('btn_archivar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            document.getElementById('btn_tarea_asignar').style.display = '';
            document.getElementById('btn_asociar_documento').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
        }
        if (estado==3) {  //No enviados
            document.getElementById('btn_enviom').style.display = '';
            document.getElementById('btn_envioe').style.display = '';
            document.getElementById('btn_enviarFisico').style.display = 'none';
        }
        if(estado==5) {  //Impresion y Envio manual            
            document.getElementById('btn_enviar').style.display = '';
            document.getElementById('btn_eliminar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_ImprimirSobre').style.display = '';
        }
        if (estado==6) {  //Enviados
            document.getElementById('btn_informar').style.display = '';
            document.getElementById('btn_archivar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_ImprimirSobre').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
            document.getElementById('btn_asociar_documento').style.display = '';
        }
        if(estado==7 || estado==84) {  //Eliminados
            document.getElementById('btn_eliminar').style.display = '';
            document.getElementById('btn_noeliminar').style.display = '';
            document.getElementById('btn_enviarFisico').style.display = 'none';
        }
        if(estado==12) {  //Tramitados
//            document.getElementById('sin_botones').style.display = 'none';
            document.getElementById('btn_informar').style.display = '';
             document.getElementById('btn_recuperar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            document.getElementById('btn_ImprimirSobre').style.display = 'none';

        }
        if(estado==13) {  //Informados
            document.getElementById('btn_informar').style.display = '';
            document.getElementById('btn_desinformar').style.display = '';
            document.getElementById('btn_enviarFisico').style.display = 'none';
            document.getElementById('btn_ImprimirSobre').style.display = 'none';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
        }
        if(estado==20) {  //Informados
            document.getElementById('btn_devolver').style.display = '';
            document.getElementById('btn_comentar').style.display = 'none';
            document.getElementById('btn_enviarFisico').style.display = 'none';
            document.getElementById('btn_ImprimirSobre').style.display = 'none';
            
        }
        if(estado==100) {  //Ciudadanos   
                 
            //document.getElementById('sin_botones').style.display = 'none';            
            document.getElementById('btn_imgRegresar').style.display ='';
            document.getElementById('btn_enviarFisico').style.display = 'none';
            document.getElementById('btn_copiar_documento').style.display = 'none';
            if(carpeta==12) {
                document.getElementById('btn_informar').style.display = '';
                if ('<?=$_GET["tipo_ventana"]?>'!='popup') document.getElementById('btn_recuperar').style.display = '';
                if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
            }
//            document.getElementById('btn_img').style.display = 'none';
        }
        if(estado==14) {  //Bandeja compartido solo Documentos Recibidos
            document.getElementById('btn_tramitar').style.display = '';
            if ('<?=$pagactual?>'=='1'){ 
               document.getElementById('btn_responder').style.display = '';
               document.getElementById('compResponder').value=1;//bandera de compartida responder
            }
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_enviarFisico').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
            document.getElementById('btn_tarea_asignar').style.display = '';
        }
        if(estado==15 || estado==16) {  //Tareas Recibidas y Enviadas
            document.getElementById('btn_tarea_asignar').style.display = '';
            if ('<?=$pagactual?>'=='1' && '<?=(0+$datosrad["estado"])?>'=='2') document.getElementById('btn_responder').style.display = '';
            document.getElementById('btn_comentar').style.display = 'none';
            if (estado==15)
                document.getElementById('txt_fech_tarea').value = 1;
            else
                document.getElementById('txt_fech_tarea').value = 16;
            document.getElementById('btn_asociar_documento').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_copiar_documento').style.display = '';
        }
        if (estado==82) {  //Ciudadanos Firma - En edicion
            document.getElementById('btn_eliminar').style.display = '';
            if ('<?=$pagactual?>'=='1') document.getElementById('btn_editar').style.display = '';
            document.getElementById('btn_firmar').style.display = '';
         }
        if (estado==85) {  //Ciudadanos Firma - No enviados
            document.getElementById('btn_envioe').style.display = '';
        }
        if (estado==90) {  //Ciudadanos Firma - Enviar documento al funcionario
            document.getElementById('btn_enviar_ciudadano').style.display = '';
        }    
    }
<!--/script-->
