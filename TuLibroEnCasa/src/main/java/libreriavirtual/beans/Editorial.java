package libreriavirtual.beans;

import java.io.Serializable;

public class Editorial implements Serializable {
    private static final long serialVersionUID = 1L;

    private int idEditorial;
    private String nombreEditorial;

    public Editorial() {}

    public Editorial(int idEditorial, String nombreEditorial) {
        this.idEditorial = idEditorial;
        this.nombreEditorial = nombreEditorial;
    }

    public int getIdEditorial() {
        return idEditorial;
    }

    public void setIdEditorial(int idEditorial) {
        this.idEditorial = idEditorial;
    }

    public String getNombreEditorial() {
        return nombreEditorial;
    }

    public void setNombreEditorial(String nombreEditorial) {
        this.nombreEditorial = nombreEditorial;
    }
}
