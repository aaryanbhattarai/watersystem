# train_model.py
"""
Script to train and evaluate ML models for the Inventory Demand Forecasting project.
This script uses training.csv to train Linear Regression and Random Forest Regressor models.
It evaluates the models using a 90/10 Train/Test Split and saves the trained models.
"""

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.preprocessing import LabelEncoder
import joblib
import os
import sys

# --- 1. Configuration ---
TRAINING_DATA_PATH = 'training.csv'
MODEL_SAVE_DIR = '.'  # Save models in the current directory

# --- 2. Data Loading and Preprocessing ---
def load_and_preprocess_data(filepath):
    """
    Loads and preprocesses the training data from a CSV file.
    Encodes categorical variables and prepares features (X) and target (y).
    """
    print(f"[INFO] Loading training data from '{filepath}'...")
    if not os.path.exists(filepath):
        print(f"[ERROR] Training data file '{filepath}' not found.", file=sys.stderr)
        sys.exit(1)

    try:
        df = pd.read_csv(filepath)
        print(f"[INFO] Data loaded successfully. Shape: {df.shape}")
    except Exception as e:
        print(f"[ERROR] Failed to read CSV file: {e}", file=sys.stderr)
        sys.exit(1)

    # Handle categorical variables: Category, Promotion
    try:
        le_category = LabelEncoder()
        le_promotion = LabelEncoder()
        
        df['Category_encoded'] = le_category.fit_transform(df['Category'])
        df['Promotion_encoded'] = le_promotion.fit_transform(df['Promotion'])
        print("[INFO] Categorical variables encoded.")
    except Exception as e:
        print(f"[ERROR] Failed to encode categorical variables: {e}", file=sys.stderr)
        sys.exit(1)

    # Extract Year and Month from 'Time' column
    try:
        df['Time'] = pd.to_datetime(df['Time'])
        df['Year'] = df['Time'].dt.year
        df['Month'] = df['Time'].dt.month
        print("[INFO] Time feature extracted into Year and Month.")
    except Exception as e:
        print(f"[ERROR] Failed to process 'Time' column: {e}", file=sys.stderr)
        sys.exit(1)

    # Features and target selection
    feature_columns = [
        'Product_ID', 'Category_encoded', 'Price ($)', 'Stock_Level', 'Promotion_encoded', 'Year', 'Month'
    ]

    missing_features = set(feature_columns) - set(df.columns)
    if missing_features:
        print(f"[ERROR] Missing feature columns in training data: {missing_features}", file=sys.stderr)
        sys.exit(1)

    X = df[feature_columns]
    y = df['Monthly_Sales']

    print(f"[INFO] Features (X) shape: {X.shape}")
    print(f"[INFO] Target (y) shape: {y.shape}")
    
    return X, y, le_category, le_promotion

# --- 3. Model Training and Evaluation ---
def train_and_evaluate_models(X, y):
    """
    Splits data (90% train, 10% test), trains Linear Regression and Random Forest models,
    evaluates them using MAE, RMSE, and R2, and returns results.
    """
    # Use test_size=0.10 for a 90/10 split
    print("\n[INFO] Splitting data into training and testing sets (90/10)...")
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.10, random_state=42
    )
    print(f"[INFO] Training set size: {X_train.shape[0]} samples")
    print(f"[INFO] Testing set size: {X_test.shape[0]} samples")

    models = {
        "Linear Regression": LinearRegression(),
        "Random Forest": RandomForestRegressor(n_estimators=100, random_state=42)
    }
    
    trained_models = {}
    results = {}

    print("\n[INFO] Training and evaluating models...")
    for name, model in models.items():
        print(f"\n--- Training {name} ---")
        try:
            model.fit(X_train, y_train)
            trained_models[name] = model
            print(f"[INFO] {name} trained successfully.")
            
            y_pred = model.predict(X_test)

            mae = mean_absolute_error(y_test, y_pred)
            rmse = np.sqrt(mean_squared_error(y_test, y_pred))
            r2 = r2_score(y_test, y_pred)

            results[name] = {
                'MAE': mae,
                'RMSE': rmse,
                'R2': r2
            }

            print(f"[RESULT] {name} Performance on Test Set:")
            print(f"  MAE: {mae:.4f}")
            print(f"  RMSE: {rmse:.4f}")
            print(f"  R2: {r2:.4f}")

        except Exception as e:
            print(f"[ERROR] Failed to train or evaluate {name}: {e}", file=sys.stderr)
            continue
            
    return trained_models, results, X_test, y_test

# --- 4. Save Models and Encoders ---
def save_models_and_encoders(trained_models, le_category, le_promotion, save_dir):
    """
    Saves the trained models and label encoders to disk using joblib.
    """
    print(f"\n[INFO] Saving trained models and encoders to '{save_dir}'...")
    try:
        joblib.dump(le_category, os.path.join(save_dir, 'le_category.pkl'))
        joblib.dump(le_promotion, os.path.join(save_dir, 'le_promotion.pkl'))
        print("[INFO] Label encoders saved.")

        for name, model in trained_models.items():
            filename = f"{name.lower().replace(' ', '_')}_model.pkl"
            filepath = os.path.join(save_dir, filename)
            joblib.dump(model, filepath)
            print(f"[INFO] {name} model saved as '{filename}'.")

        print("[INFO] All models and encoders saved successfully.")
    except Exception as e:
        print(f"[ERROR] Failed to save models or encoders: {e}", file=sys.stderr)
        sys.exit(1)

# --- 5. Main Execution ---
def main():
    """Main function to orchestrate the training process."""
    print("--- Inventory Demand Forecasting - Model Training ---")
    
    X, y, le_category, le_promotion = load_and_preprocess_data(TRAINING_DATA_PATH)
    
    trained_models, evaluation_results, X_test, y_test = train_and_evaluate_models(X, y)
    
    if not trained_models:
        print("[ERROR] No models were successfully trained. Exiting.", file=sys.stderr)
        sys.exit(1)

    save_models_and_encoders(trained_models, le_category, le_promotion, MODEL_SAVE_DIR)

    print("\n--- Final Model Comparison Summary (on Test Set) ---")
    print(f"{'Model':<20} {'MAE':<12} {'RMSE':<12} {'R2':<12}")
    print("-" * 55)
    for name, metrics in evaluation_results.items():
        print(f"{name:<20} {metrics['MAE']:<12.4f} {metrics['RMSE']:<12.4f} {metrics['R2']:<12.4f}")
    print("-" * 55)

    best_model_name = max(evaluation_results, key=lambda k: evaluation_results[k]['R2'])
    best_r2 = evaluation_results[best_model_name]['R2']
    print(f"\n[INFO] Based on R2 Score, the best performing model is: {best_model_name} (R2 = {best_r2:.4f})")
    print("[INFO] Models are ready for use in forecasting.")

if __name__ == "__main__":
    main()
