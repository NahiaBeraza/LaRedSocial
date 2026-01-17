package frontendSimple;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.util.List;

import dao.LibroDAO;
import dao.AutorDAO;
import dao.CategoriaDAO;
import dao.EditorialDAO;

import model.Libro;
import model.Autor;
import model.Categoria;
import model.Editorial;

/**
 * CRUD simple para gestionar libros.
 * Versión básica sin estilos, ideal para aprender Swing.
 */
public class LibroUISimple extends JFrame {

    private static final long serialVersionUID = 1L;

    private LibroDAO libroDAO = new LibroDAO();
    private AutorDAO autorDAO = new AutorDAO();
    private CategoriaDAO categoriaDAO = new CategoriaDAO();
    private EditorialDAO editorialDAO = new EditorialDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public LibroUISimple() {

        setTitle("Gestión de Libros");
        setSize(700, 400);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);

        setLayout(new BorderLayout());

        modeloTabla = new DefaultTableModel(
                new Object[]{"ISBN", "Título", "Autor", "Categoría", "Editorial", "Precio", "Stock"},
                0
        );

        tabla = new JTable(modeloTabla);
        add(new JScrollPane(tabla), BorderLayout.CENTER);

        JPanel panelBotones = new JPanel(new FlowLayout());

        JButton btnAñadir = new JButton("Añadir");
        JButton btnModificar = new JButton("Modificar");
        JButton btnBorrar = new JButton("Borrar");

        panelBotones.add(btnAñadir);
        panelBotones.add(btnModificar);
        panelBotones.add(btnBorrar);

        add(panelBotones, BorderLayout.SOUTH);

        btnAñadir.addActionListener(e -> añadirLibro());
        btnModificar.addActionListener(e -> modificarLibro());
        btnBorrar.addActionListener(e -> borrarLibro());

        cargarTabla();
        setVisible(true);
    }

    private void cargarTabla() {
        modeloTabla.setRowCount(0);

        List<Libro> libros = libroDAO.listar();

        for (Libro l : libros) {
            modeloTabla.addRow(new Object[]{
                    l.getIsbn(),
                    l.getTitulo(),
                    l.getAutor().getNombreAutor(),
                    l.getCategoria().getNombreCategoria(),
                    l.getEditorial().getNombreEditorial(),
                    l.getPrecioUnitario(),
                    l.getStock()
            });
        }
    }

    private void añadirLibro() {

        try {
            String isbn = JOptionPane.showInputDialog(this, "ISBN:");
            String titulo = JOptionPane.showInputDialog(this, "Título:");

            Autor autor = seleccionarAutor();
            Categoria categoria = seleccionarCategoria();
            Editorial editorial = seleccionarEditorial();

            double precioUnitario = Double.parseDouble(JOptionPane.showInputDialog(this, "Precio unitario:"));
            int stock = Integer.parseInt(JOptionPane.showInputDialog(this, "Stock:"));

            // CONSTRUCTOR CORRECTO
            Libro libro = new Libro(isbn, titulo, precioUnitario, stock, autor, categoria, editorial);

            libroDAO.insertar(libro);
            cargarTabla();

        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Datos inválidos.");
        }
    }

    private void modificarLibro() {

        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un libro.");
            return;
        }

        // ISBN ES STRING
        String isbn = (String) tabla.getValueAt(fila, 0);
        Libro libro = libroDAO.buscar(isbn);

        String nuevoTitulo = JOptionPane.showInputDialog(this, "Nuevo título:", libro.getTitulo());
        Autor nuevoAutor = seleccionarAutor();
        Categoria nuevaCategoria = seleccionarCategoria();
        Editorial nuevaEditorial = seleccionarEditorial();

        double nuevoPrecio = Double.parseDouble(JOptionPane.showInputDialog(this, "Nuevo precio:", libro.getPrecioUnitario()));
        int nuevoStock = Integer.parseInt(JOptionPane.showInputDialog(this, "Nuevo stock:", libro.getStock()));

        libro.setTitulo(nuevoTitulo);
        libro.setAutor(nuevoAutor);
        libro.setCategoria(nuevaCategoria);
        libro.setEditorial(nuevaEditorial);
        libro.setPrecioUnitario(nuevoPrecio);
        libro.setStock(nuevoStock);

        libroDAO.modificar(libro);
        cargarTabla();
    }

    private void borrarLibro() {

        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un libro.");
            return;
        }

        // ISBN ES STRING
        String isbn = (String) tabla.getValueAt(fila, 0);

        int confirm = JOptionPane.showConfirmDialog(
                this,
                "¿Seguro que deseas borrar este libro?",
                "Confirmar borrado",
                JOptionPane.YES_NO_OPTION
        );

        if (confirm == JOptionPane.YES_OPTION) {
            boolean borrado = libroDAO.borrar(isbn);

            if (!borrado) {
                JOptionPane.showMessageDialog(this, "No se puede borrar.");
            }

            cargarTabla();
        }
    }

    private Autor seleccionarAutor() {
        List<Autor> autores = autorDAO.listar();
        return (Autor) JOptionPane.showInputDialog(
                this,
                "Selecciona un autor:",
                "Autores",
                JOptionPane.QUESTION_MESSAGE,
                null,
                autores.toArray(),
                autores.get(0)
        );
    }

    private Categoria seleccionarCategoria() {
        List<Categoria> categorias = categoriaDAO.listar();
        return (Categoria) JOptionPane.showInputDialog(
                this,
                "Selecciona una categoría:",
                "Categorías",
                JOptionPane.QUESTION_MESSAGE,
                null,
                categorias.toArray(),
                categorias.get(0)
        );
    }

    private Editorial seleccionarEditorial() {
        List<Editorial> editoriales = editorialDAO.listar();
        return (Editorial) JOptionPane.showInputDialog(
                this,
                "Selecciona una editorial:",
                "Editoriales",
                JOptionPane.QUESTION_MESSAGE,
                null,
                editoriales.toArray(),
                editoriales.get(0)
        );
    }
}
