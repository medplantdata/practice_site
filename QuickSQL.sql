CREATE TABLE natural_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    SMILES TEXT,
    source VARCHAR(255)
);

SELECT * FROM natural_products;