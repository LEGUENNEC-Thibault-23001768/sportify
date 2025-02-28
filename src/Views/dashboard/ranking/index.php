<div data-view="ranking">
    <div class="ranking-container">
        <h1>Classement des utilisateurs</h1>

        <div class="filter-section">
            <label for="sport">Sport :</label>
            <select id="sport">
                <option value="all">Tous les sports</option>
                <option value="rpm">RPM</option>
                <option value="musculation">Musculation</option>
                <option value="boxe">Boxe</option>
                <option value="football">Football</option>
                <option value="tennis">Tennis</option>
                <option value="basketball">Basketball</option>
            </select>
            <select id="sort-column">
                <option value="total_score">Score Total</option>
                <option value="total_calories">Calories Total</option>
                <option value="total_play_time_seconds">Temps Total</option>
            </select>

            <label>Ordre :</label>
            <input type="radio" id="sort-asc" name="sort-order" value="asc">
            <label for="sort-asc">Croissant</label>
            <input type="radio" id="sort-desc" name="sort-order" value="desc" checked>
            <label for="sort-desc">Décroissant</label>

            <button id="apply-filter">Appliquer</button>
        </div>

        <table class="table table-bordered">
        <thead>
            <tr>
                <th>Rang</th>
                <th>Nom</th>
                <th>Score Total</th>
                <th>Calories Total</th>
                <th>Temps Total</th>
            </tr>
        </thead>
            <tbody id="ranking-table-body"></tbody>
        </table>
    </div>
</div>
