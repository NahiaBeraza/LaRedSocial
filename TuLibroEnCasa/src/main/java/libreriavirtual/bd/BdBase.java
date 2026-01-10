package libreriavirtual.bd;

import java.sql.Connection;
import java.sql.DriverManager;

import libreriavirtual.config.Configuracion;

public class BdBase {

    protected Connection conexion;

    protected BdBase() {
        super();
    }

    public boolean abrirConexion() {
        boolean correcto = true;
        try {
            // Obtener la configuración cargada desde el properties
            Configuracion cfg = Configuracion.getInstancia();

            // Cargar el driver
            Class.forName(cfg.getDriver());

            // Abrir la conexión
            conexion = DriverManager.getConnection(
                    cfg.getUrl(),
                    cfg.getUser(),
                    cfg.getPassword()
            );

        } catch (Exception e) {
            e.printStackTrace();
            correcto = false;
        }
        return correcto;
    }

    public boolean cerrarConexion() {
        boolean correcto = true;
        try {
            conexion.close();
        } catch (Exception e) {
            e.printStackTrace();
            correcto = false;
        }
        return correcto;
    }

    public boolean abrirTransaccion() {
        boolean correcto = true;
        try {
            conexion.setAutoCommit(false);
        } catch (Exception e) {
            e.printStackTrace();
            correcto = false;
        }
        return correcto;
    }

    public boolean hacerCommit() {
        boolean correcto = true;
        try {
            conexion.commit();
        } catch (Exception e) {
            e.printStackTrace();
            correcto = false;
        }
        return correcto;
    }

    public boolean hacerRollback() {
        boolean correcto = true;
        try {
            conexion.rollback();
        } catch (Exception e) {
            e.printStackTrace();
            correcto = false;
        }
        return correcto;
    }
    //para poder acceder a la conexion en SrvPedido y hacerlo como Transaccion
    public void setConexion(Connection c) {
        this.conexion = c;
    }
    public Connection getConexion() {
        return conexion;
    }



}
