package ec.gob.firmadigital.servicio.util;

import java.net.URI;
import java.net.URISyntaxException;
import java.util.HashMap;
import java.util.Map;

import ec.gob.firmadigital.firmaec_app.exceptions.ProtocoloInvalidoException;

public class ProtocoloFirmaDigital {
    private final String SISTEMA;
    private final String OPERACION;
    private final Map<String, String> PARAMETROS;
    private static final String PROTOCOLO = "firmaec";

    public ProtocoloFirmaDigital(String str) throws ProtocoloInvalidoException {
        URI uri;
        try {
            uri = new URI(str);
        } catch (URISyntaxException e) {
            throw new ProtocoloInvalidoException(e);
        }
        if (!PROTOCOLO.equals(uri.getScheme())) {
            throw new ProtocoloInvalidoException("Solo se soporta el protocolo '" + PROTOCOLO + "'");
        }
        if (uri.getAuthority() == null || uri.getQuery() == null || uri.getQuery().isEmpty()) {
            throw new ProtocoloInvalidoException("Se debe incluir un sistema en el protocolo");
        }
        if (uri.getPath() == null || uri.getQuery() == null || uri.getQuery().isEmpty()) {
            throw new ProtocoloInvalidoException("Se debe incluir una operacion en el protocolo");
        }
        if (uri.getQuery() == null || uri.getQuery() == null || uri.getQuery().isEmpty()) {
            throw new ProtocoloInvalidoException("Se deben incluir parámetros en el protocolo");
        }
        this.SISTEMA = uri.getAuthority();
        this.OPERACION = uri.getPath();
        this.PARAMETROS = parseQuery(uri.getQuery());
    }

    public String getSistema() {
        return SISTEMA;
    }

    public String getOperacion() {
        return OPERACION;
    }

    public Map<String, String> getParametros() {
        return PARAMETROS;
    }

    /**
     * Analiza el query y extrae un Map<String, String> con los parametros y sus
     * valores.
     */
    private Map<String, String> parseQuery(String query) {
        Map<String, String> map = new HashMap<>();
        String[] parameters = query.split("&");
        for (String param : parameters) {
            String[] valores = param.split("=");
            String name = valores[0];
            String value = (valores.length == 2 ? valores[1] : null);
            map.put(name, value);
        }
        return map;
    }
}