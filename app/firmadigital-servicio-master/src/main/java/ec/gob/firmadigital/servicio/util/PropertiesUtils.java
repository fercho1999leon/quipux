/* 
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
package ec.gob.firmadigital.servicio.util;

import java.io.IOException;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author mfernandez
 */
public class PropertiesUtils {

    private static final String CONFIG = "config.servicio.properties";
    private static Properties config;

    public static Properties getConfig() {
        config = new Properties();
        try {
            config.load(PropertiesUtils.class.getClassLoader().getResourceAsStream(CONFIG));
        } catch (IOException ex) {
            Logger.getLogger(PropertiesUtils.class.getName()).log(Level.SEVERE, null, ex);
        }
        return config;
    }

    public static String getDocumentoKB() {
        getConfig();
        return config.getProperty("documentoKB");
    }
}
