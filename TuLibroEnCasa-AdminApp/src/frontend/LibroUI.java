package frontend;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;

import dao.LibroDAO;
import dao.AutorDAO;
import dao.CategoriaDAO;
import dao.EditorialDAO;

import model.Libro;
import model.Autor;
import model.Categoria;
import model.Editorial;

import java.util.List;

/**
 * Versión estilizada de la ventana CRUD de libros.
 * Inspirada en el CSS proporcionado.
 */
public class LibroUI extends JFrame {

    private static final long serialVersionUID = 1L;

    private LibroDAO libroDAO = new LibroDAO();
    private AutorDAO autorDAO = new AutorDAO();
    private CategoriaDAO categoriaDAO = new CategoriaDAO();
    private EditorialDAO editorialDAO = new EditorialDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public LibroUI() {

        // ============================
        // CONFIGURACIÓN DE LA VENTANA
        // ============================
        setTitle("Gestión de Libros");
        setSize(900, 500);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);

        // Fondo morado
        getContentPane().setBackground(Color.decode("#7b6cff"));
        setLayout(new GridBagLayout());

        // ============================
        // CARD PRINCIPAL
        // ============================
        JPanel card = new JPanel();
        card.setBackground(Color.WHITE);
        card.setPreferredSize(new Dimension(820, 420));
        card.setLayout(new BorderLayout(20, 20));
        card.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        // ============================
        // TÍTULO
        // ============================
        JLabel titulo = new JLabel(
                "<html><h2 style='color:#6c63ff;'>Gestión de Libros</h2></html>"
        );
        titulo.setHorizontalAlignment(SwingConstants.CENTER);

        card.add(titulo, BorderLayout.NORTH);

        // ============================
        // TABLA ESTILIZADA
        // ============================
        modeloTabla = new DefaultTableModel(
                new Object[]{"ISBN", "Título", "Autor", "Categoría", "Editorial", "Precio", "Stock"},
                0
        );

        tabla = new JTable(modeloTabla);

        tabla.getTableHeader().setBackground(Color.decode("#6c63ff"));
        tabla.getTableHeader().setForeground(Color.WHITE);
        tabla.getTableHeader().setFont(new Font("SansSerif", Font.BOLD, 14));

        tabla.setRowHeight(24);

        JScrollPane scroll = new JScrollPane(tabla);
        card.add(scroll, BorderLayout.CENTER);

        // ============================
        // BOTONES ESTILO CSS
        // ============================
        JPanel panelBotones = new JPanel(new GridLayout(1, 3, 10, 10));
        panelBotones.setBackground(Color.WHITE);

        JButton btnAñadir = crearBoton("Añadir");
        JButton btnModificar = crearBoton("Modificar");
        JButton btnBorrar = crearBoton("Borrar");

        panelBotones.add(btnAñadir);
        panelBotones.add(btnModificar);
        panelBotones.add(btnBorrar);

        card.add(panelBotones, BorderLayout.SOUTH);

        add(card);

        // ============================
        // ACCIONES CRUD
        // ============================
        btnAñadir.addActionListener(e -> añadirLibro());
        btnModificar.addActionListener(e -> modificarLibro());
        btnBorrar.addActionListener(e -> borrarLibro());

        cargarTabla();
        setVisible(true);
    }

    // ============================
    // BOTÓN ESTILIZADO
    // ============================
    private JButton crearBoton(String texto) {
        JButton btn = new JButton(texto);

        btn.setBackground(Color.decode("#6c63ff"));
        btn.setForeground(Color.WHITE);
        btn.setFocusPainted(false);
        btn.setFont(new Font("SansSerif", Font.BOLD, 14));
        btn.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        btn.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseEntered(MouseEvent e) {
                btn.setBackground(Color.decode("#584ff5"));
            }

            @Override
            public void mouseExited(MouseEvent e) {
                btn.setBackground(Color.decode("#6c63ff"));
            }
        });

        return btn;
    }

    // ============================
    // CRUD
    // ============================
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

    // ============================
    // MÉTODOS AUXILIARES PARA COMBOS
    // ============================
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
