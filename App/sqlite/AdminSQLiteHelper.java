package org.hopto.fundacioncgr.fundacioncgr.sqlite;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;


/**
 * Clase para crear la base de datos en SQLite para la aplicación de Android.
 */
public class AdminSQLiteHelper extends SQLiteOpenHelper {

    public static final String DATABASE_NAME = "fundacioncgr.db";
    public static final int DATABASE_VERSION = 1;

    /**
     * Constructor de la clase que crea la base de datos si no existe previamente.
     * @param context contexto desde donde se crear el objeto.
     */
    public AdminSQLiteHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    /**
     * Método para crear las tablas la primera vez que se accede a la base de de datos.
     * @param db Base de datos SQLite.
     */
    @Override
    public void onCreate(SQLiteDatabase db) {
        String guiaPersonalizada = "CREATE TABLE guiapersonalizada (\n " +
                " `#guia` INTEGER PRIMARY KEY AUTOINCREMENT\n," +
                " nombre_guia varchar(120) DEFAULT NULL\n" +
                ");";

        String codigosGuiasPersonalizadas = "CREATE TABLE codigosguiaspersonalizadas (\n " +
                " `#guia` INTEGER PRIMARY KEY\n" +
                ");";

        String compuesta=" CREATE TABLE Compuesta (\n" +
                "`#recurso` INTEGER NOT NULL,\n" +
                "`#guia` INTEGER NOT NULL,\n" +
                "PRIMARY KEY(`#guia`, `#recurso`)\n" +
                ");";

        String recursos="CREATE TABLE Recursos (\n" +
                "  `#recurso` INTEGER NOT NULL,\n" +
                "  codigo_qr INTEGER NOT NULL,\n" +
                "  nombre TEXT NOT NULL,\n" +
                "PRIMARY KEY(`#recurso`)\n" +
                ");";

        String sql_multimedia = "CREATE TABLE IF NOT EXISTS reproducir_contenido (\n" +
                "url TEXT NOT NULL,\n" +
                "timestamp INTEGER DEFAULT(0),\n" +
                "PRIMARY KEY(url)\n" +
                ");";

        db.execSQL(sql_multimedia);
        db.execSQL(guiaPersonalizada);
        db.execSQL(compuesta);
        db.execSQL(recursos);
        db.execSQL(codigosGuiasPersonalizadas);
    }

    /**
     * Método que se ejecuta cuando se ha actualizado la estructura de la base de datos de una versión a otra de la aplicación.
     * @param db Base de datos SQLite.
     * @param oldVersion versión antigua de la base de datos que usaba la aplicación.
     * @param newVersion versión de la base de datos a la cual queremos actualizar.
     */
    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {

    }
}
