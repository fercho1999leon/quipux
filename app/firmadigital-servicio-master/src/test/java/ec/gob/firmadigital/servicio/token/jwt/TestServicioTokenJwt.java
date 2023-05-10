/*
 * Firma Digital: Servicio
 * Copyright 2017 Secretaría Nacional de la Administración Pública
 *
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

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertTrue;
import static org.junit.Assert.fail;

import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;

import javax.crypto.SecretKey;

import org.junit.Test;

import ec.gob.firmadigital.servicio.token.TokenExpiradoException;
import ec.gob.firmadigital.servicio.token.TokenInvalidoException;

/**
 * Pruebas de unidad de ServicioTokenJwt.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
public class TestServicioTokenJwt {

	@Test
	public void testParseToken() throws Exception {
		ServicioTokenJwt servicioToken = new ServicioTokenJwt();
		servicioToken.init();

		Map<String, Object> parametros = new HashMap<>();
		parametros.put("a", 1);
		String token = servicioToken.generarToken(parametros);

		Map<String, Object> parametros2 = servicioToken.parseToken(token);
		assertEquals(parametros, parametros2);
	}

	@Test
	public void testParseTokenConExpiracion() throws Exception {
		ServicioTokenJwt servicioToken = new ServicioTokenJwt();
		servicioToken.init();

		Map<String, Object> parametros = new HashMap<>();
		parametros.put("a", 1);

		LocalDateTime before = LocalDateTime.now().minusMinutes(1);
		Date date = Date.from(before.toInstant(ZoneOffset.of("-05:00")));

		String token = servicioToken.generarToken(parametros, date);

		try {
			servicioToken.parseToken(token);
			fail();
		} catch (TokenExpiradoException e) {
		}
	}

	@Test
	public void testParseTokenSinExpiracion() throws Exception {
		ServicioTokenJwt servicioToken = new ServicioTokenJwt();
		servicioToken.init();

		Map<String, Object> parametros = new HashMap<>();
		parametros.put("a", 1);

		LocalDateTime before = LocalDateTime.now().plusMinutes(15);
		Date date = Date.from(before.toInstant(ZoneOffset.of("-05:00")));

		String token = servicioToken.generarToken(parametros, date);

		try {
			servicioToken.parseToken(token);
			Map<String, Object> parametros2 = servicioToken.parseToken(token);
			assertEquals(1, parametros2.get("a"));
		} catch (TokenExpiradoException e) {
			fail();
		}
	}

	@Test
	public void testParseTokenInvalido() throws Exception {
		ServicioTokenJwt servicioToken = new ServicioTokenJwt();
		servicioToken.init();

		try {
			// Token firmado con otra llave secreta
			servicioToken.parseToken("eyJhbGciOiJIUzI1NiJ9.eyJhIjoxfQ.co8628bPk8NFBhogFsOCaBCWM0hEUv0exdhMmMOPe2k");
			fail();
		} catch (TokenInvalidoException e) {
		}

		try {
			// Token sin firma
			servicioToken.parseToken("eyJhbGciOiJIUzI1NiJ9.eyJhIjoxfQ");
			fail();
		} catch (TokenInvalidoException e) {
		}

		try {
			// Token mal formado
			servicioToken.parseToken("gfdgfdgfdgfdgfdgfd.gfdgfdgfdgfdgfdgfd.gfdgfdgfdg");
			fail();
		} catch (TokenInvalidoException e) {
		}
	}

	@Test
	public void testSecretKey() throws Exception {
		SecretKey secretKey1 = ServicioTokenJwt.generarLlaveSecreta();
		String keyBase64 = ServicioTokenJwt.codificarLlaveSecreta(secretKey1);
		SecretKey secretKey2 = ServicioTokenJwt.decodificarLlaveSecreta(keyBase64);
		assertTrue(secretKey1.equals(secretKey2));
	}
}