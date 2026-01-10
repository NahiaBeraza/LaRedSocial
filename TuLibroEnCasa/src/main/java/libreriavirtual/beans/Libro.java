package libreriavirtual.beans;

import java.io.Serializable;

public class Libro implements Serializable {
    private static final long serialVersionUID = 1L;

    // Datos principales
    private String isbn;
    private String titulo;
    private double precioUnitario;
    private int stock;

    // Claves foráneas
    private int idAutor;
    private int idCategoria;
    private int idEditorial;

    // Nombres reales (JOIN con Autor, Categoria, Editorial)
    private String nombreAutor;
    private String nombreCategoria;
    private String nombreEditorial;

    public Libro() {}

    public Libro(String isbn, String titulo, double precioUnitario, int stock,
                 int idAutor, int idCategoria, int idEditorial) {
        this.isbn = isbn;
        this.titulo = titulo;
        this.precioUnitario = precioUnitario;
        this.stock = stock;
        this.idAutor = idAutor;
        this.idCategoria = idCategoria;
        this.idEditorial = idEditorial;
    }

    // Getters y setters

    public String getIsbn() {
        return isbn;
    }

    public void setIsbn(String isbn) {
        this.isbn = isbn;
    }

    public String getTitulo() {
        return titulo;
    }

    public void setTitulo(String titulo) {
        this.titulo = titulo;
    }

    public double getPrecioUnitario() {
        return precioUnitario;
    }

    public void setPrecioUnitario(double precioUnitario) {
        this.precioUnitario = precioUnitario;
    }

    public int getStock() {
        return stock;
    }

    public void setStock(int stock) {
        this.stock = stock;
    }

    public int getIdAutor() {
        return idAutor;
    }

    public void setIdAutor(int idAutor) {
        this.idAutor = idAutor;
    }

    public int getIdCategoria() {
        return idCategoria;
    }

    public void setIdCategoria(int idCategoria) {
        this.idCategoria = idCategoria;
    }

    public int getIdEditorial() {
        return idEditorial;
    }

    public void setIdEditorial(int idEditorial) {
        this.idEditorial = idEditorial;
    }

    public String getNombreAutor() {
        return nombreAutor;
    }

    public void setNombreAutor(String nombreAutor) {
        this.nombreAutor = nombreAutor;
    }

    public String getNombreCategoria() {
        return nombreCategoria;
    }

    public void setNombreCategoria(String nombreCategoria) {
        this.nombreCategoria = nombreCategoria;
    }

    public String getNombreEditorial() {
        return nombreEditorial;
    }

    public void setNombreEditorial(String nombreEditorial) {
        this.nombreEditorial = nombreEditorial;
    }
}
