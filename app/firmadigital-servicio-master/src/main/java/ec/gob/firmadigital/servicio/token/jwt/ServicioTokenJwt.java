/*
 * Firma Digital: Servicio
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package ec.gob.firmadigital.servicio.token.jwt;

import java.util.Date;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.annotation.PostConstruct;
import javax.crypto.SecretKey;
import javax.crypto.spec.SecretKeySpec;
import javax.ejb.Singleton;
import javax.ejb.Startup;

import ec.gob.firmadigital.servicio.token.ServicioToken;
import ec.gob.firmadigital.servicio.token.TokenExpiradoException;
import ec.gob.firmadigital.servicio.token.TokenInvalidoException;
import ec.gob.firmadigital.servicio.util.Base64InvalidoException;
import ec.gob.firmadigital.servicio.util.Base64Util;
import io.jsonwebtoken.Claims;
import io.jsonwebtoken.ExpiredJwtException;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.MalformedJwtException;
import io.jsonwebtoken.MissingClaimException;
import io.jsonwebtoken.SignatureAlgorithm;
import io.jsonwebtoken.SignatureException;
import io.jsonwebtoken.UnsupportedJwtException;
import io.jsonwebtoken.impl.DefaultClaims;
import io.jsonwebtoken.impl.crypto.MacProvider;
import javax.ejb.Lock;
import javax.ejb.LockType;

/**
 * Servicio para trabajar con tokens tipo JWT (https://jwt.io).
 *
 * La llave secreta para firmar los tokens se genera al iniciar la aplicacion.
 * Sin embargo, se puede almacenar una version en Base64 de la llave en el
 * archivo de configuracion del servidor WildFly (standalone.xml), asi:
 *
 * <pre>
 *   <system-properties>
 *     <property name="jwt.key" value= "Jgh46..." />
 *   </system-properties>
 * </pre>
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Singleton
@Startup
@Lock(LockType.READ)
public class ServicioTokenJwt implements ServicioToken {

    private static final Logger LOGGER = Logger.getLogger(ServicioTokenJwt.class.getName());

    /**
     * Llave secreta para firmar los tokens
     */
    private SecretKey secretKey;

    /**
     * Algoritmo de firma HMAC por defecto
     */
    private static final SignatureAlgorithm DEFAULT_SIGNATURE_ALGORITHM = SignatureAlgorithm.HS512;

    /**
     * Nombre de la propiedad de sistema que contiene la llave secreta, en
     * formato Base64
     */
    private static final String KEY_SYSTEM_PROPERTY = "jwt.key";

    @PostConstruct
    public void init() {
        LOGGER.info("Inicializando llave secreta para tokens JWT...");
        String keyBase64 = System.getProperty(KEY_SYSTEM_PROPERTY);

        if (keyBase64 != null) {
            LOGGER.info("Se encontro la propiedad de sistema \"jwt.key\"");

            try {
                // Cargar la llave secreta
                this.secretKey = decodificarLlaveSecreta(keyBase64);
                LOGGER.info("Se creo una llave secreta a partir de la propiedad de sistema \"jwt.key\"");
                return;
            } catch (Throwable e) {
                LOGGER.log(Level.SEVERE,
                        "ERROR: No se pudo crear una llave secreta a partir de la propiedad \"jwt.key\"", e);
            }
        }

        // Llave secreta autogenerada
        this.secretKey = generarLlaveSecreta();
        LOGGER.info("Se creo una llave secreta autogenerada");
    }

    /**
     * @param parametros
     * @see
     * ec.gob.firmadigital.servicio.ServicioToken#generarToken(java.util.Map)
     */
    @Override
    public String generarToken(Map<String, Object> parametros) {
        return generarToken(parametros, null);
    }

    /**
     * @param parametros
     * @see
     * ec.gob.firmadigital.servicio.ServicioToken#generarToken(java.util.Map,
     * java.util.Date)
     */
    @Override
    public String generarToken(Map<String, Object> parametros, Date expiracion) {
        Claims claims = new DefaultClaims(parametros);
        return Jwts.builder().setClaims(claims).signWith(DEFAULT_SIGNATURE_ALGORITHM, secretKey)
                .setExpiration(expiracion).compact();
    }

    /**
     * @see
     * ec.gob.firmadigital.servicio.ServicioToken#parseToken(java.lang.String)
     */
    @Override
    public Map<String, Object> parseToken(String token) throws TokenInvalidoException, TokenExpiradoException {
        try {
            return Jwts.parser().setSigningKey(secretKey).parseClaimsJws(token).getBody();
        } catch (MalformedJwtException | SignatureException | UnsupportedJwtException | IllegalArgumentException | MissingClaimException e) {
            throw new TokenInvalidoException(e);
        } catch (ExpiredJwtException e) {
            throw new TokenExpiradoException(e);
        }
    }

    /**
     * Genera una llave secreta para firmar los tokens.
     *
     * @return
     */
    public static SecretKey generarLlaveSecreta() {
        return MacProvider.generateKey(DEFAULT_SIGNATURE_ALGORITHM);
    }

    /**
     * Genera una llave privada randómica para configurar como variable dentro
     * del archivo standalone.xml del servidor de aplicaciones WildFly/JBoss.
     *
     * Esta llave debe ser configurada en el servidor de aplicaciones WildFly,
     * en el archivo standalone.xml, en la sección <system-properties>:
     *
     * <pre>
     *  ...
     *  </extensions>
     *  <system-properties>
     *    <property name="jwt.key" value="tYdX9if...=="/>
     *  </system-properties>
     *  <management>
     *  ...
     * </pre>
     *
     * @param key
     * @return una llave privada en formato Base 64.
     */
    public static String codificarLlaveSecreta(SecretKey key) {
        return Base64Util.encode(key.getEncoded());
    }

    /**
     * Decodificar llave secreta en Base 64.
     *
     * @param keyBase64
     * @return
     * @throws Base64InvalidoException
     */
    public static SecretKey decodificarLlaveSecreta(String keyBase64) throws Base64InvalidoException {
        return new SecretKeySpec(Base64Util.decode(keyBase64), DEFAULT_SIGNATURE_ALGORITHM.getJcaName());
    }

    // Generar llave secreta randomica en Base 64
    public static void main(String[] args) {
        SecretKey key = generarLlaveSecreta();
        System.out.println("jwt.key: " + codificarLlaveSecreta(key));
    }
}
