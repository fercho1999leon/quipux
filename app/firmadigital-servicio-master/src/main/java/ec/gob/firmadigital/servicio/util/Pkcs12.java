/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package ec.gob.firmadigital.servicio.util;

import io.rubrica.keystore.Alias;
import io.rubrica.keystore.FileKeyStoreProvider;
import io.rubrica.keystore.KeyStoreProvider;
import io.rubrica.keystore.KeyStoreUtilities;
import java.io.ByteArrayInputStream;
import java.io.InputStream;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.util.Base64;
import java.util.List;

/**
 *
 * @author mfernandez
 */
public class Pkcs12 {
    
    public static KeyStore getKeyStore(String pkcs12, String password) throws KeyStoreException {
        byte encodedPkcs12[] = Base64.getDecoder().decode(pkcs12);
        InputStream inputStreamPkcs12 = new ByteArrayInputStream(encodedPkcs12);

        KeyStoreProvider ksp = new FileKeyStoreProvider(inputStreamPkcs12);
        return ksp.getKeystore(password.toCharArray());
    }

    public static String getAlias(KeyStore keyStore) {
        List<Alias> signingAliases = KeyStoreUtilities.getSigningAliases(keyStore);
        return signingAliases.get(0).getAlias();
    }
}
