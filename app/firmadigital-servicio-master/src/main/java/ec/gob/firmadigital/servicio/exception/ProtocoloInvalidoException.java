package ec.gob.firmadigital.firmaec_app.exceptions;

import java.net.URISyntaxException;

public class ProtocoloInvalidoException extends Exception {

    private static final long serialVersionUID = 6491132940384665796L;

    public ProtocoloInvalidoException(String message) {
        super(message);
    }

    public ProtocoloInvalidoException(String message, URISyntaxException e) {
        super(message, e);
    }

    public ProtocoloInvalidoException(URISyntaxException e) {
        super(e);
    }
}