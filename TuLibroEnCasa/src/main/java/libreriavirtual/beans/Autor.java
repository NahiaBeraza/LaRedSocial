package libreriavirtual.beans;

import java.io.Serializable;

public class Autor implements Serializable {
    private static final long serialVersionUID = 1L;

    private int idAutor;
    private String nombreAutor;

    public Autor() {}

	public Autor(int idAutor, String nombreAutor) {
		this.idAutor = idAutor;
		this.nombreAutor = nombreAutor;
	}

	public int getIdAutor() {
		return idAutor;
	}

	public void setIdAutor(int idAutor) {
		this.idAutor = idAutor;
	}

	public String getNombreAutor() {
		return nombreAutor;
	}

	public void setNombreAutor(String nombreAutor) {
		this.nombreAutor = nombreAutor;
	}

    
}
