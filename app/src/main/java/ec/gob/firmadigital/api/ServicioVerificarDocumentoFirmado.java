/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/WebServices/GenericResource.java to edit this template
 */
package ec.gob.firmadigital.api;

import javax.ws.rs.core.Context;
import javax.ws.rs.core.UriInfo;
import javax.ws.rs.Produces;
import javax.ws.rs.Consumes;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PUT;
import javax.ws.rs.core.MediaType;

/**
 * REST Web Service
 *
 * @author barckl3y
 */
@Path("verificardocumentofirmado")
public class ServicioVerificarDocumentoFirmado {

    @Context
    private UriInfo context;

    /**
     * Creates a new instance of ServicioVerificarDocumentoFirmado
     */
    public ServicioVerificarDocumentoFirmado() {
    }

    /**
     * Retrieves representation of an instance of ec.gob.firmadigital.api.ServicioVerificarDocumentoFirmado
     * @return an instance of java.lang.String
     */
    @GET
    @Produces(MediaType.APPLICATION_XML)
    public String getXml() {
        //TODO return proper representation object
        throw new UnsupportedOperationException();
    }

    /**
     * PUT method for updating or creating an instance of ServicioVerificarDocumentoFirmado
     * @param content representation for the resource
     */
    @PUT
    @Consumes(MediaType.APPLICATION_XML)
    public void putXml(String content) {
    }
}
