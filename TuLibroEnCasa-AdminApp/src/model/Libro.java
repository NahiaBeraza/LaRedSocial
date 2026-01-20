package model;

import java.io.Serializable;
import javax.persistence.*;
/**
 * The persistent class for the categoria database table.
 * 
 */
@Entity
@Table(name = "Libro")
public class Libro implements Serializable {
    private static final long serialVersionUID = 1L;

    @Id
    @Column(name = "isbn")
    private String isbn;

    @Column(name = "titulo", nullable = false)
    private String titulo;

    @Column(name = "precio_unitario", nullable = false)
    private double precioUnitario;

    @Column(name = "stock", nullable = false)
    private int stock;

    @ManyToOne
    @JoinColumn(name = "id_autor")//con el JoinColumn indicamos la FK
    private Autor autor;

    @ManyToOne
    @JoinColumn(name = "id_categoria")
    private Categoria categoria;

    @ManyToOne
    @JoinColumn(name = "id_editorial")
    private Editorial editorial;

    public Libro() {}

    public Libro(String isbn, String titulo, double precioUnitario, int stock,
                 Autor autor, Categoria categoria, Editorial editorial) {
        this.isbn = isbn;
        this.titulo = titulo;
        this.precioUnitario = precioUnitario;
        this.stock = stock;
        this.autor = autor;
        this.categoria = categoria;
        this.editorial = editorial;
    }

    // Getters y setters

    public String getIsbn() { return isbn; }
    public void setIsbn(String isbn) { this.isbn = isbn; }

    public String getTitulo() { return titulo; }
    public void setTitulo(String titulo) { this.titulo = titulo; }

    public double getPrecioUnitario() { return precioUnitario; }
    public void setPrecioUnitario(double precioUnitario) { this.precioUnitario = precioUnitario; }

    public int getStock() { return stock; }
    public void setStock(int stock) { this.stock = stock; }

    public Autor getAutor() { return autor; }
    public void setAutor(Autor autor) { this.autor = autor; }

    public Categoria getCategoria() { return categoria; }
    public void setCategoria(Categoria categoria) { this.categoria = categoria; }

    public Editorial getEditorial() { return editorial; }
    public void setEditorial(Editorial editorial) { this.editorial = editorial; }

    @Override
    public String toString() {
        return "Libro [isbn=" + isbn + ", titulo=" + titulo +
               ", precioUnitario=" + precioUnitario +
               ", stock=" + stock +
               ", autor=" + autor.getNombreAutor() +
               ", categoria=" + categoria.getNombreCategoria() +
               ", editorial=" + editorial.getNombreEditorial() + "]";
    }
}
